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
            $gamesController = new GamesController();
            $cambios = 0;

            foreach ($response['matches'] as $key => $partido) {
                $juegoAntes = Juegos::where('api_id', $partido['id'])->first();
                $estatusAntes = $juegoAntes?->estatus;

                $juego = Juegos::updateOrCreate(
                    [
                        'api_id' => $partido['id']
                    ],
                    [
                        'equipo1' => $partido['homeTeam']['name'],
                        'equipo2' => $partido['awayTeam']['name'],
                        'resultadoEquipo1' => $partido['score']['fullTime']['home'] ?? 0,
                        'resultadoEquipo2' => $partido['score']['fullTime']['away'] ?? 0,
                        'imagenEquipo1' => $partido['homeTeam']['crest'],
                        'imagenEquipo2' => $partido['awayTeam']['crest'],
                        'estatus' => $partido['status'],
                        'ronda' => $partido['stage'],
                        'competicion' => $partido['competition']['code'],
                        'season' => isset($partido['season']['startDate'])
                            ? Carbon::parse($partido['season']['startDate'])->year
                            : null,
                        'nombreCompeticion' => $partido['competition']['name'] ?? null,
                        'fechaJuego' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s'),
                        'horaJuego' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s')
                    ]
                );

                $estatusNuevo = $partido['status'];
                $enJuego = in_array($estatusNuevo, ['IN_PLAY', 'PAUSED']);
                $cambioDeEstatus = $estatusAntes !== $estatusNuevo;
                $acabaDeTerminar = $cambioDeEstatus && $estatusNuevo === 'FINISHED';

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
                'partidos_procesados' => count($response['matches']),
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
