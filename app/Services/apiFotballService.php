<?php

namespace App\Services;

use App\Http\Controllers\GamesController;
use App\Models\Juegos;
use App\Models\Partidos;
use App\Models\Torneo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class apiFotballService
{
    
    
    public function sincronizarTodos(): void
    {
        $codigos = Torneo::activos();

        if ($codigos->isEmpty()) {
            Log::channel('sync')->info('[sync] Sin torneos activos configurados');
            return;
        }

        foreach ($codigos as $codigo) {
            $this->sincronizarJugos($codigo);
        }
    }

    public function sincronizarJugos($torneo)
    {
        $url = "https://api.football-data.org/v4/competitions/{$torneo}/matches";
        
        Log::channel('sync')->info("[$torneo] Iniciando sincronización");

        $response = Http::withHeaders([
            'X-Auth-Token' => env('FOOTBALL_API_KEY'),
        ])->get($url);

        if ($response->successful()) {
            $data = $response->json();

            if (empty($data['matches'])) {
                Log::channel('sync')->warning("[$torneo] Respuesta sin 'matches'", ['body' => $response->body()]);
                return ['success' => false, 'message' => 'Sin partidos en la respuesta', 'data' => null];
            }

            $gamesController = new GamesController();
            $cambios = 0;

            foreach ($data['matches'] as $key => $partido) {
                $juegoAntes = Juegos::where('api_id', $partido['id'])->first();
                $estatusAntes = $juegoAntes?->estatus;

                $scoreHome = $partido['score']['fullTime']['home'];
                $scoreAway = $partido['score']['fullTime']['away'];

                $statusApi = $partido['status'];

                // No regresar de IN_PLAY/PAUSED/FINISHED a TIMED — el API free tier puede fluctuar
                $statusNoRegresa = ['IN_PLAY', 'PAUSED', 'FINISHED'];
                $statusEfectivo = (in_array($estatusAntes, $statusNoRegresa) && $statusApi === 'TIMED')
                    ? $estatusAntes
                    : $statusApi;

                $campos = [
                    'equipo1' => $partido['homeTeam']['name'],
                    'equipo2' => $partido['awayTeam']['name'],
                    'imagenEquipo1' => $partido['homeTeam']['crest'],
                    'imagenEquipo2' => $partido['awayTeam']['crest'],
                    'estatus' => $statusEfectivo,
                    'ronda' => $partido['stage'],
                    'competicion' => $partido['competition']['code'],
                    'season' => isset($partido['season']['startDate'])
                        ? Carbon::parse($partido['season']['startDate'])->year
                        : null,
                    'nombreCompeticion' => $partido['competition']['name'] ?? null,
                    'fechaJuego' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s'),
                    'horaJuego' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s'),
                ];

                // Solo actualizar marcador cuando el partido termina — fullTime solo es confiable en FINISHED
                if ($statusApi === 'FINISHED' && $scoreHome !== null && $scoreAway !== null) {
                    $campos['resultadoEquipo1'] = $scoreHome;
                    $campos['resultadoEquipo2'] = $scoreAway;
                }

                $juego = Juegos::updateOrCreate(['api_id' => $partido['id']], $campos);

                $estatusNuevo = $statusEfectivo;
                $enJuego = in_array($estatusNuevo, ['IN_PLAY', 'PAUSED']);
                $cambioDeEstatus = $estatusAntes !== $estatusNuevo;
                $acabaDeTerminar = $cambioDeEstatus && $statusApi === 'FINISHED';

                if ($enJuego || $acabaDeTerminar) {
                    $gamesController->ApiActualizarPuntaje($juego->id, $acabaDeTerminar);
                    $cambios++;

                    if ($cambioDeEstatus) {
                        Log::channel('sync')->info("[$torneo] Cambio de estatus", [
                            'partido' => $partido['homeTeam']['name'] . ' vs ' . $partido['awayTeam']['name'],
                            'antes'   => $estatusAntes,
                            'ahora'   => $estatusNuevo,
                            'marcador' => ($partido['score']['fullTime']['home'] ?? 0) . '-' . ($partido['score']['fullTime']['away'] ?? 0),
                        ]);
                    }
                }
            }

            Log::channel('sync')->info("[$torneo] Sincronización completada", [
                'partidos_procesados' => count($data['matches']),
                'puntajes_actualizados' => $cambios,
            ]);

            return [
                'success' => true,
                'message' => 'Sincronizado correctamente',
                'data' => null
            ];
        }

        if ($response->failed()) {
            Log::channel('sync')->error("[$torneo] Error al sincronizar", [
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

    public function iniciarPartido(GamesController $juegos){
        $respuesta = $juegos->iniciarPartido(222, "IN_PLAY");

        // Log::alert($respuesta);
    }
}
