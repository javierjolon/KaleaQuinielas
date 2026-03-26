<?php

namespace App\Services;

use App\Http\Controllers\GamesController;
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
                Partidos::updateOrCreate(
                    [
                        'api_id' => $partido['id']
                    ],
                    [
                        'team1' => $partido['homeTeam']['name'],
                        'imagenTeam1' => $partido['homeTeam']['crest'],
                        'score1' => 0 ,
                        'team2' => $partido['awayTeam']['name'],
                        'imagenTeam2' => $partido['awayTeam']['crest'],
                        'score2' => 0,
                        'typeGame' => $partido['stage'],
                        'status' => $partido['status'],
                        'dateGame' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s'),
                        'timeGame' => Carbon::parse($partido['utcDate'])->setTimezone('America/Guatemala')->format('Y-m-d H:i:s')
                    ]
                );
            }

            

            Log::alert('API Success', 'Actualizado correctamente');

            return [
                'success' => true,
                'message' => 'Sincronizado correctamente',
                'data' => null
            ];
        }

        if ($response->failed()) {
            Log::error('API Error', [
                'status' => $response->status(),
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
        $respuesta = $juegos->iniciarPartido();

        Log::alert($respuesta);
    }
}
