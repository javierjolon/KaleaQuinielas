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
                $juegoAntes  = Juegos::where('api_id', $fixture['fixture']['id'])->first();
                $estatusAntes = $juegoAntes?->estatus;

                $statusShort = $fixture['fixture']['status']['short'] ?? 'NS';
                $statusApi   = $this->mapearEstatus($statusShort);

                $scoreHome = $fixture['goals']['home'];
                $scoreAway = $fixture['goals']['away'];

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

                // Solo actualizar marcador al finalizar
                if ($statusApi === 'FINISHED' && $scoreHome !== null && $scoreAway !== null) {
                    $campos['resultadoEquipo1'] = $scoreHome;
                    $campos['resultadoEquipo2'] = $scoreAway;
                }

                $juego = Juegos::updateOrCreate(['api_id' => $fixture['fixture']['id']], $campos);

                $estatusNuevo    = $statusEfectivo;
                $enJuego         = in_array($estatusNuevo, ['IN_PLAY', 'PAUSED']);
                $cambioDeEstatus = $estatusAntes !== $estatusNuevo;
                $acabaDeTerminar = $cambioDeEstatus && $statusApi === 'FINISHED';

                if ($enJuego || $acabaDeTerminar) {
                    $gamesController->ApiActualizarPuntaje($juego->id, $acabaDeTerminar);
                    $cambios++;

                    if ($cambioDeEstatus) {
                        Log::channel('sync')->info("[{$label}] Cambio de estatus", [
                            'partido' => $fixture['teams']['home']['name'] . ' vs ' . $fixture['teams']['away']['name'],
                            'antes'   => $estatusAntes,
                            'ahora'   => $estatusNuevo,
                            'marcador' => ($scoreHome ?? 0) . '-' . ($scoreAway ?? 0),
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
}
