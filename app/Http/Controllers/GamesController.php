<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class GamesController extends Controller
{
    public function iniciarPartido($partidoId, $estatus){
        DB::table('game')
            ->where('id', '=', $partidoId)
            ->update([
                'estatus' => $estatus
            ]);
    }

    public function actualizarQuiniela(){
        $partidos = DB::table('game')
            ->where('game.fechaJuego', '=', Carbon::today())
            ->where('game.estatus', '=', 'IN_PLAY')
            ->orWhere('game.estatus', '=', 'LIVE')
            ->orWhere('game.estatus', '=', 'PAUSED')
            ->get();

        if (count($partidos) > 0) {
            foreach ($partidos as $key => $partido) {
                DB::table('quiniela')
                ->where('juegoId', $partido->id)
                ->where('estatus', '!=', 'INVALID')
                ->update([
                    'estatus' => 'IN_PLAY'
                ]);
            };
        }
        return; 
    }


    public function index(){
        $juegos = DB::table('game')
        ->orderBy('fechaJuego')
        ->orderBy('horaJuego')
        ->get()
        ->map(function ($game) {
            $game->equipo1 = traducir_equipos($game->equipo1 ?? "Pendiente");
            $game->equipo2 = traducir_equipos($game->equipo2 ?? "Pendiente");
            $game->estatus = traducir_estatus($game->estatus ?? " ");
            $game->tipoJuego = traducir_rondas($game->tipoJuego ?? " ");
            return $game;
        });
        // dd($juegos);
        return Inertia::render('Games/index', ['juegos' => $juegos]);
    }

    // public function setGame()
    // {
    //     $equipo1 = request()->get('equipo1');
    //     $equipo2 = request()->get('equipo2');
    //     $date = request()->get('date');
    //     $time = request()->get('time');
    //     $type = request()->get('type');

    //     DB::table('game')->insert(
    //         [
    //             'equipo1' => $equipo1,
    //             'resultadoEquipo1' => null,
    //             'equipo2' => $equipo2,
    //             'resultadoEquipo2' => null,
    //             'tipoJuego' => $type,
    //             'fechaJuego' => $date,
    //             'horaJuego' => $time,
    //             'created_at' => date('Y-m-d H:i:s'),
    //             'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
    //         ]
    //     );

    //     return back();
    // }

    // public function addResult(){
    //     $games = DB::table('game')
    //         ->join('team as t1', 't1.id', '=', 'game.equipo1')
    //         ->join('team as t2', 't2.id', '=', 'game.equipo2')
    //         ->join('estatusgame as sg', 'game.estatus', '=', 'sg.id')
    //         ->select(
    //             't1.name as equipo1',
    //             't2.name as equipo2',
    //             'game.fechaJuego',
    //             'game.horaJuego',
    //             'game.id',
    //             'game.estatus',
    //             'game.resultadoEquipo1',
    //             'game.resultadoEquipo2',
    //             'sg.name as estatusname'
    //         )
    //         ->orderBy('fechaJuego','asc')
    //         ->orderBy('horaJuego', 'asc')
    //         ->get();

    //     return view('games/addResult', compact('games'));
    // }

    public function setResultGame(){

        $juegoId = request()->get('juegoId');
        $resultadoEquipo1 = request()->get('resultadoEquipo1');
        $resultadoEquipo2 = request()->get('resultadoEquipo2');

        DB::table('game')
            ->where('id', '=', $juegoId)
            ->update(['resultadoEquipo1' => $resultadoEquipo1, 'resultadoEquipo2' => $resultadoEquipo2]);

        $quinielas = DB::table('quiniela')
            ->where('juegoId', '=', $juegoId)
            ->get();

        $puntosXjuego = 0;

        foreach($quinielas as $quiniela){
            if ($resultadoEquipo1 == $quiniela->quinielaEquipo1){
                $puntosXjuego++;
            }
            if ($resultadoEquipo2 == $quiniela->quinielaEquipo2){
                $puntosXjuego++;
            }

            $winMatch = $this->analizeGame($resultadoEquipo1, $resultadoEquipo2);
            $winQuiniela = $this->analizeGame($quiniela->quinielaEquipo1, $quiniela->quinielaEquipo2);

            if ($winMatch == $winQuiniela){
                $puntosXjuego++;
            }

            DB::table('quiniela')
                ->where('id', '=', $quiniela->id)
                ->update([ 'puntosXjuego' => $puntosXjuego ]);

            $userPoint = DB::table('quiniela')
                ->where('usuarioId', '=', $quiniela->usuarioId)
                ->sum('puntosXjuego');

            DB::table('users')
                ->where('id', '=', $quiniela->usuarioId)
                ->update(['accumulatedPointsTemp' => $userPoint]);

            $puntosXjuego = 0;
        }

        $this->updateTempPosition();

        return redirect()->action('GamesController@initGame', ['juegoId' => $juegoId]);
    }

    private function updateTempPosition(){

        $users = DB::table('users')
            ->where('id', '<>', '1')
            ->orderBy('accumulatedPointsTemp', 'desc')
            ->get();

        foreach ($users as $clave => $user) {
            if ($user->actualPositionTemp > $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPositionTemp' => intval($clave +1), 'upDownTemp' => 's']);

            }elseif ($user->actualPositionTemp == $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPositionTemp' => intval($clave +1), 'upDownTemp' => 'i']);

            }elseif($user->actualPositionTemp < $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPositionTemp' => intval($clave +1), 'upDownTemp' => 'b']);
            }
        }
    }

    private function updateActualPostition(){

        $users = DB::table('users')
            ->where('id', '<>', '1')
            ->orderBy('accumulatedPoints', 'desc')
            ->get();

        foreach ($users as $clave => $user) {
            if ($user->actualPosition > $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPosition' => intval($clave +1), 'upDown' => 's']);

            }elseif ($user->actualPosition == $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPosition' => intval($clave +1), 'upDown' => 'i']);

            }elseif($user->actualPosition < $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPosition' => intval($clave +1), 'upDown' => 'b']);
            }
        }
    }

    private function analizeGame($resultadoEquipo1, $resultadoEquipo2){
        if ($resultadoEquipo1 > $resultadoEquipo2){
            return 'G1';
        }elseif($resultadoEquipo1 == $resultadoEquipo2){
            return 'E';
        }elseif($resultadoEquipo1 < $resultadoEquipo2){
            return 'G2';
        }
    }

    public function initGame(){
        $gamesId = request()->get('juegoId');

        $games = DB::table('game')
            ->join('team as t1', 't1.id', '=', 'game.equipo1')
            ->join('team as t2', 't2.id', '=', 'game.equipo2')
            ->join('estatusgame as sg', 'game.estatus', '=', 'sg.id')
            ->select(
                't1.name as equipo1',
                't1.image as img1',
                't2.name as equipo2',
                't2.image as img2',
                'game.resultadoEquipo1',
                'game.resultadoEquipo2',
                'game.fechaJuego',
                'game.horaJuego',
                'game.id',
                'game.estatus',
                'sg.name as estatusname'
            )
            ->where('game.id', '=', $gamesId)
            ->orderBy('fechaJuego','asc')
            ->orderBy('horaJuego', 'asc')
            ->first();

        return view("games/initGame", compact('games'));
    }

    public function endGame(){
        $juegoId = request()->get('juegoId');

        DB::table('game')
            ->where('id', '=', $juegoId)
            ->update([ 'estatus' => 3 ]);

//        $quinielas = DB::table('game')
//            ->leftJoin('quiniela as q', 'game.id', '=' , 'q.juegoId')
//            ->select(
//                'q.id as quinielaId',
//                'game.resultadoEquipo1 as final1',
//                'game.resultadoEquipo2 as final2',
//                'q.quinielaEquipo1 as quiniela1',
//                'q.quinielaEquipo2 as quiniela2',
//                'q.usuarioId'
//            )
//            ->where('game.id', '=', $juegoId)
//            ->get();
//
//        $puntosXjuego = 0;
//
//        foreach($quinielas as $quiniela){
//            if ($quiniela->final1 == $quiniela->quiniela1){
//                $puntosXjuego++;
//            }
//            if ($quiniela->final2 == $quiniela->quiniela2){
//                $puntosXjuego++;
//            }
//
//            $winMatch = $this->analizeGame($quiniela->final1, $quiniela->final2);
//            $winQuiniela = $this->analizeGame($quiniela->quiniela1, $quiniela->quiniela2);
//
//            if ($winMatch == $winQuiniela){
//                $puntosXjuego++;
//            }
//
//            DB::table('quiniela')
//                ->where('id', '=', $quiniela->quinielaId)
//                ->update([ 'puntosXjuego' => $puntosXjuego ]);
//
//            $accumulatedPoints = DB::table('users')
//                ->select('accumulatedPoints')
//                ->where('id', '=', $quiniela->usuarioId)->first();
//
//            DB::table('users')
//                ->where('id', '=', $quiniela->usuarioId)
//                ->update(['accumulatedPoints' => intval($puntosXjuego + $accumulatedPoints->accumulatedPoints)]);
//
//            $puntosXjuego = 0;
//        }
//        $this->updateActualPostition();

        return back()->with('success', 'Actualizado correctamente');
    }

    public function starGame(){
        $gamesId = request()->get('juegoId');

        DB::table('game')
            ->where('id', '=', $gamesId)
            ->update(['estatus' => 2]);

        return redirect()->action('GamesController@initGame', ['juegoId' => $gamesId]);
    }
}
