<?php

namespace App\Services;

use App\Http\Controllers\GamesController;
use App\Models\Juegos;
use App\Models\Torneo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class apiFotballService
{
    public function sincronizarTodos(): void
    {
        $torneos = Torneo::activos();

        if ($torneos->isEmpty()) {
            Log::channel('sync')->info('[sync] Sin torneos activos configurados');
            return;
        }

        foreach ($torneos as $torneo) {
            $this->sincronizarJugos($torneo);
        }
    }

    public function sincronizarJugos($torneo)
    {
        $leagueId = $torneo->codigo;
        $season   = $torneo->season;
        $label    = "{$torneo->nombre} [{$leagueId}/{$season}]";

        $url = "https://v3.football.api-sports.io/fixtures?league={$leagueId}&season={$season}";

        Log::channel('sync')->info("[{$label}] Iniciando sincronización");

        $response = Http::withHeaders([
            'x-apisports-key' => env('FOOTBALL_API_KEY'),
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();

            if (empty($data['response'])) {
                Log::channel('sync')->warning("[{$label}] Respuesta sin 'response'", ['body' => $response->body()]);
                return ['success' => false, 'message' => 'Sin partidos en la respuesta', 'data' => null];
            }

            $gamesController = new GamesController();
            $cambios = 0;

            foreach ($data['response'] as $fixture) {
                $apiId       = $fixture['fixture']['id'];

                // Priorizar registro legacy (football-data.org) sobre registro huérfano nuevo
                $legacyJuego = $this->buscarJuegoPorFecha($fixture);
                $juegoAntes  = $legacyJuego ?? Juegos::where('api_id', $apiId)->first();
                $estatusAntes = $juegoAntes?->estatus;

                $statusShort = $fixture['fixture']['status']['short'] ?? 'NS';
                $statusApi   = $this->mapearEstatus($statusShort);

                // score.fulltime = solo 90 min (goals incluye ET, no cuenta penales)
                $scoreHome = $fixture['score']['fulltime']['home'] ?? $fixture['goals']['home'];
                $scoreAway = $fixture['score']['fulltime']['away'] ?? $fixture['goals']['away'];

                // Free tier puede no enviar IN_PLAY — misma protección que antes
                $statusNoRegresa = ['IN_PLAY', 'PAUSED', 'FINISHED'];
                $fechaJuego      = Carbon::parse($fixture['fixture']['date']);
                $juegoYaEmpezó   = $fechaJuego->isPast();

                if (in_array($estatusAntes, $statusNoRegresa) && $statusApi === 'TIMED') {
                    $statusEfectivo = $estatusAntes;
                } elseif ($statusApi === 'TIMED' && $juegoYaEmpezó) {
                    $statusEfectivo = 'IN_PLAY';
                } else {
                    $statusEfectivo = $statusApi;
                }

                $campos = [
                    'equipo1'          => $fixture['teams']['home']['name'],
                    'equipo2'          => $fixture['teams']['away']['name'],
                    'imagenEquipo1'    => $fixture['teams']['home']['logo'],
                    'imagenEquipo2'    => $fixture['teams']['away']['logo'],
                    'estatus'          => $statusEfectivo,
                    'ronda'            => $fixture['league']['round'] ?? null,
                    'competicion'      => (string) $fixture['league']['id'],
                    'season'           => $fixture['league']['season'],
                    'nombreCompeticion' => $fixture['league']['name'] ?? null,
                    'fechaJuego'       => Carbon::parse($fixture['fixture']['date'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s'),
                    'horaJuego'        => Carbon::parse($fixture['fixture']['date'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s'),
                ];

                if (in_array($statusApi, ['FINISHED', 'IN_PLAY', 'PAUSED']) && $scoreHome !== null && $scoreAway !== null) {
                    $campos['resultadoEquipo1'] = $scoreHome;
                    $campos['resultadoEquipo2'] = $scoreAway;
                }

                // Si encontramos un registro legacy (api_id distinto), actualizar en lugar de crear
                if ($juegoAntes && $juegoAntes->api_id !== $apiId) {
                    // Eliminar registro huérfano creado por sync previo con nuevo api_id
                    Juegos::where('api_id', $apiId)->where('id', '!=', $juegoAntes->id)->delete();
                    $juegoAntes->update(array_merge($campos, ['api_id' => $apiId]));
                    $juego = $juegoAntes->fresh();
                } else {
                    $juego = Juegos::updateOrCreate(['api_id' => $apiId], $campos);
                }

                if ($juego->wasRecentlyCreated) {
                    Log::channel('sync')->info("[{$label}] Juego nuevo registrado", [
                        'partido' => $campos['equipo1'] . ' vs ' . $campos['equipo2'],
                        'estatus' => $statusEfectivo,
                        'fecha'   => Carbon::parse($campos['fechaJuego'])->format('d/m H:i'),
                    ]);
                }

                $estatusNuevo    = $statusEfectivo;
                $enJuego         = in_array($estatusNuevo, ['IN_PLAY', 'PAUSED']);
                $cambioDeEstatus = $estatusAntes !== $estatusNuevo;
                $acabaDeTerminar = $cambioDeEstatus && $statusApi === 'FINISHED';

                // Recalcular si el marcador cambió en partido ya finalizado (e.g. sync llegó tarde)
                $scoreChanged = $statusApi === 'FINISHED'
                    && $scoreHome !== null && $scoreAway !== null
                    && ((int) ($juegoAntes?->resultadoEquipo1) !== $scoreHome
                        || (int) ($juegoAntes?->resultadoEquipo2) !== $scoreAway);

                // Marcador cambia durante partido en juego
                $scoreMidgameChanged = $enJuego
                    && $scoreHome !== null && $scoreAway !== null
                    && ((int) ($juegoAntes?->resultadoEquipo1) !== $scoreHome
                        || (int) ($juegoAntes?->resultadoEquipo2) !== $scoreAway);

                if ($enJuego || $acabaDeTerminar || $scoreChanged) {
                    $gamesController->ApiActualizarPuntaje($juego->id, $acabaDeTerminar || $scoreChanged);
                    $cambios++;

                    if ($cambioDeEstatus || $scoreChanged) {
                        Log::channel('sync')->info("[{$label}] " . ($cambioDeEstatus ? 'Cambio de estatus' : 'Marcador corregido'), [
                            'partido'  => $fixture['teams']['home']['name'] . ' vs ' . $fixture['teams']['away']['name'],
                            'antes'    => $estatusAntes,
                            'ahora'    => $estatusNuevo,
                            'marcador' => ($scoreHome ?? 0) . '-' . ($scoreAway ?? 0),
                        ]);
                    } elseif ($scoreMidgameChanged) {
                        Log::channel('sync')->info("[{$label}] Marcador en juego actualizado", [
                            'partido'  => $fixture['teams']['home']['name'] . ' vs ' . $fixture['teams']['away']['name'],
                            'marcador' => $scoreHome . '-' . $scoreAway,
                            'estatus'  => $estatusNuevo,
                        ]);
                    }
                }
            }

            Log::channel('sync')->info("[{$label}] Sincronización completada", [
                'partidos_procesados'  => count($data['response']),
                'puntajes_actualizados' => $cambios,
            ]);

            return [
                'success' => true,
                'message' => 'Sincronizado correctamente',
                'data'    => null,
            ];
        }

        if ($response->failed()) {
            Log::channel('sync')->error("[{$label}] Error al sincronizar", [
                'status_code' => $response->status(),
                'body'        => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al sincronizar: HTTP ' . $response->status(),
                'data'    => null,
            ];
        }
    }

    private function mapearEstatus(string $short): string
    {
        return match($short) {
            'NS', 'TBD'              => 'TIMED',
            '1H', '2H', 'ET', 'P',
            'INT', 'LIVE'            => 'IN_PLAY',
            'HT', 'BT'               => 'PAUSED',
            'FT', 'AET', 'PEN'       => 'FINISHED',
            'SUSP'                   => 'SUSPENDED',
            'PST'                    => 'POSTPONED',
            'CANC', 'ABD'            => 'CANCELLED',
            'AWD', 'WO'              => 'AWARDED',
            default                  => $short,
        };
    }

    public function iniciarPartido(GamesController $juegos)
    {
        $respuesta = $juegos->iniciarPartido(222, 'IN_PLAY');
    }

    /**
     * Busca un juego legacy por fecha+hora+equipo cuando el api_id cambió de proveedor.
     * Mapeo: api-sports.io ID → código viejo (football-data.org).
     */
    private function buscarJuegoPorFecha(array $fixture): ?Juegos
    {
        $codigosLegacy = ['1' => 'WC', '140' => 'PD', '2' => 'CL'];
        $leagueId      = (string) $fixture['league']['id'];

        if (!isset($codigosLegacy[$leagueId])) {
            return null;
        }

        $codigoViejo = $codigosLegacy[$leagueId];
        $fechaLocal  = Carbon::parse($fixture['fixture']['date'])->setTimezone('America/Guatemala');
        $fecha       = $fechaLocal->format('Y-m-d');
        $hora        = $fechaLocal->format('H:i:s');
        $primeraWord = explode(' ', $fixture['teams']['home']['name'])[0];

        // Buscar por fecha+hora+primer_palabra_del_equipo_local dentro del código legacy
        $candidatos = Juegos::where('competicion', $codigoViejo)
            ->whereDate('fechaJuego', $fecha)
            ->whereTime('horaJuego', $hora)
            ->get();

        if ($candidatos->count() === 1) {
            return $candidatos->first();
        }

        // Hay varios partidos a la misma hora: desambiguar por primera palabra del equipo local
        return $candidatos->first(function ($j) use ($primeraWord) {
            return stripos($j->equipo1, $primeraWord) !== false;
        });
    }
}
