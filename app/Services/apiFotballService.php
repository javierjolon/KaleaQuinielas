<?php

namespace App\Services;

use App\Http\Controllers\GamesController;
use App\Models\Juegos;
use App\Models\Partidos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class apiFotballService
{
    public function sincronizarJugos()
    {
        $response = Http::withHeaders([
            'X-Auth-Token' => env('FOOTBALL_API_KEY'),
        ])->get('https://api.football-data.org/v4/competitions/WC/matches');

        if ($response->successful()) {
            foreach ($response['matches'] as $key => $partido) {
                Juegos::updateOrCreate(
                    [
                        'api_id' => $partido['id']
                    ],
                    [
                        'equipo1' => $partido['homeTeam']['name'],
                        'equipo2' => $partido['awayTeam']['name'],
                        'resultadoEquipo1' => 0 ,
                        'resultadoEquipo2' => 0,
                        'imagenEquipo1' => $partido['homeTeam']['crest'],
                        'imagenEquipo2' => $partido['awayTeam']['crest'],
                        'estatus' => $partido['status'],
                        'ronda' => $partido['stage'],
                        'fechaJuego' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s'),
                        'horaJuego' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s')
                    ]
                );
            }

            

            // Log::alert('API Success', 'Actualizado correctamente');

            return [
                'success' => true,
                'message' => 'Sincronizado correctamente',
                'data' => null
            ];
        }

        if ($response->failed()) {
            Log::error('API Error', [
                'estatus' => $response->estatus(),
                'body' => $response->body()
            ]);

            abort(500, [
                'success' => false,
                'message' => 'Error al sincronizar',
                'data' => null
            ]);
        }
    }

    public function iniciarPartido(GamesController $juegos){
        $respuesta = $juegos->iniciarPartido(222, "IN_PLAY");

        // Log::alert($respuesta);
    }
}
