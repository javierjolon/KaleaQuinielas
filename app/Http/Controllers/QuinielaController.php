<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\apiFotballService;
use Carbon\Carbon;
use Inertia\Inertia;

class QuinielaController extends Controller
{
    public function index(){
        $userId = Auth::user()->id;
        $juegosPendientes = DB::table('game as g')
        ->where('g.dateGame', ">=", Carbon::today())
        ->whereNotExists(function ($query) use ($userId) {
            $query->select(DB::raw(1))
                ->from('quiniela as q')
                ->whereColumn('q.gameId', 'g.id')
                ->where('q.userId', $userId);
        })
        ->orderBy('g.dateGame')
        ->orderBy('g.timeGame')
        ->get()
        ->map(function ($game) {
            $game->team1 = traducir_equipos($game->team1 ?? "Pendiente");
            $game->team2 = traducir_equipos($game->team2 ?? "Pendiente");
            $game->status = ['nombre' => traducir_status($game->status ?? " "), 'color' => color_status($game->status ?? " ")];
            $game->typeGame = traducir_rondas($game->typeGame ?? " ");
            return $game;
        });

        $juegosIngresados = DB::table('quiniela')
        ->select("quiniela.scoreTeam1", "quiniela.scoreTeam2", "quiniela.status", 
            "game.id", "game.dateGame", "game.timeGame", "game.team1", "game.imagenTeam1", "game.team2", "game.imagenTeam2", "game.typeGame")
        ->leftJoin('game', 'quiniela.gameId', "=", "game.id")
        ->orderBy('game.dateGame')
        ->orderBy('game.timeGame')
        ->where("userId", "=", $userId)
        ->get()
        ->map(function ($game) {
            $game->team1 = traducir_equipos($game->team1 ?? "Pendiente");
            $game->team2 = traducir_equipos($game->team2 ?? "Pendiente");
            $game->status = ['nombre' => traducir_status($game->status ?? " "), 'color' => color_status($game->status ?? " ")];
            $game->typeGame = traducir_rondas($game->typeGame ?? " ");
            return $game;
        });

        $juegosFinalizados = DB::table('quiniela')
        ->select("quiniela.scoreTeam1", "quiniela.scoreTeam2", "quiniela.pointsXGame", 
            "game.id", "game.dateGame", "game.team1", "game.imagenTeam1", "game.team2", "game.imagenTeam2", "game.typeGame", "game.score1", "game.score2")
        ->leftJoin('game', 'quiniela.gameId', "=", "game.id")
        ->where("userId", "=", $userId)
        ->where("game.status", "=", "FINISHED")
        ->orderBy('game.dateGame')
        ->orderBy('game.timeGame')
        ->get()
        ->map(function ($game) {
            $game->team1 = traducir_equipos($game->team1 ?? "Pendiente");
            $game->team2 = traducir_equipos($game->team2 ?? "Pendiente");
            $game->typeGame = traducir_rondas($game->typeGame ?? " ");
            return $game;
        });

        return Inertia::render('Quiniela/quiniela', ['juegosPendientes' => $juegosPendientes, 'juegosIngresados' => $juegosIngresados, 'juegosFinalizados' => $juegosFinalizados]);
    }

    public function store(){
        $juegoId = request()->get('juego_id');
        $team1 = request()->get('team1');
        $team2 = request()->get('team2');

        $datosJuego = DB::table("game")
        ->select("dateGame", "timeGame")    
        ->where("id", "=", $juegoId)
        ->first();

        $horaJuego = Carbon::parse($datosJuego->timeGame)->subMinutes(10);

        if(Carbon::today()->lt($datosJuego->dateGame) && $datosJuego->dateGame == "TIMED"){
            DB::table('quiniela')
                ->Insert(
                [
                    'userId' => Auth::user()->id,
                    'gameId' => $juegoId,
                    'scoreTeam1' => $team1,
                    'scoreTeam2' => $team2,
                    'pointsXGame' => 0,
                    'status' => "TIMED",
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            return back();
        }elseif (Carbon::today()->eq($datosJuego->dateGame) && $datosJuego->dateGame == "TIMED"){
            if (Carbon::now()->lt($horaJuego)){
                DB::table('quiniela')
                ->Insert(
                [
                    'userId' => Auth::user()->id,
                    'gameId' => $juegoId,
                    'scoreTeam1' => $team1,
                    'scoreTeam2' => $team2,
                    'pointsXGame' => 0,
                    'status' => "TIMED",
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                return back();
            }else{
                $array = [ 'status' => 'INVALID', 'scoreTeam1' => '-1', 'scoreTeam2' => '-1', 'pointsXGame' => 0];
                $this->actualizarDB('quiniela', $juegoId, $array);
                return back()->withErrors(['error' => 'Fuera de horario']);
            }
        }else{
            $array = [ 'status' => 'INVALID', 'scoreTeam1' => '-1', 'scoreTeam2' => '-1', 'pointsXGame' => 0];
            $this->actualizarDB('quiniela', $juegoId, $array);
            return back()->withErrors(['error' => 'Fuera de horario']);
        }
    }

    public function patch($juegoId){
        $scoreTeam1 = request()->get("scoreTeam1");
        $scoreTeam2 = request()->get("scoreTeam2");
        
        $data = [ 'updated_at' => now() ];
    
        if (!is_null($scoreTeam1)) {
            $data['scoreTeam1'] = $scoreTeam1;
        }
    
        if (!is_null($scoreTeam2)) {
            $data['scoreTeam2'] = $scoreTeam2;
        }

        $datosJuego = DB::table("game")
        ->select("dateGame", "timeGame")    
        ->where("id", "=", $juegoId)
        ->first();

        $horaJuego = Carbon::parse($datosJuego->timeGame)->subMinutes(10);

        if(Carbon::today()->lt($datosJuego->dateGame)){
            $this->actualizarDB('quiniela', $juegoId, $data);
            return back();
        
        }elseif (Carbon::today()->eq($datosJuego->dateGame)){
            
            if (Carbon::now()->lt($horaJuego)){
                $this->actualizarDB('quiniela', $juegoId, $data);
                return back();
            }else{
                $array = ['status' => 'LOCKED'];
                $this->actualizarDB('quiniela', $juegoId, $array);
                return back()->withErrors(['error' => 'Fuera de horario']);
            }
        }else{
            $array = ['status' => 'LOCKED'];
            $this->actualizarDB('quiniela', $juegoId, $array);
            return back()->withErrors(['error' => 'Fuera de horario']);
        }

        return back();
    }

    public function actualizarDB($tabla, $juegoId, $arrayCampos){
        DB::table($tabla)
        ->updateOrInsert(
            ['userId'=> Auth::user()->id, 'gameId' => $juegoId],
            $arrayCampos
        );
        return;
    }

    public function pointsXgame(){
        $games = DB::table('game')
            ->join('team as t1', 't1.id', '=', 'game.team1')
            ->join('team as t2', 't2.id', '=', 'game.team2')
            ->join('statusgame as sg', 'game.status', '=', 'sg.id')
            ->select(
                't1.name as team1',
                't2.name as team2',
                'game.score1',
                'game.score2',
                'game.dateGame',
                'game.timeGame',
                'game.id',
                'game.status',
                'sg.name as nameStatusGame',
                't1.image as image1',
                't2.image as image2'
            )
            ->orderBy('dateGame','asc')
            ->orderBy('timeGame', 'asc')
            ->get();

        $results = DB::table('quiniela')
            ->where('userId', '=', Auth::user()->id)
            ->get();

//        $respuesta = DB::table('game')
//                ->where('status', '=', 2)
//                ->count('status') > 0;

        $points = DB::table('quiniela')
            ->where('userId', '=', Auth::user()->id)
            ->sum('pointsXGame');

//        if ($respuesta){
//
//            $points = DB::table('users')
//                ->select('accumulatedPointsTemp as points')
//                ->where('id', '=', Auth::user()->id)
//                ->first();
//        }else{
//            $points = DB::table('users')
//                ->select('accumulatedPoints as points')
//                ->where('id', '=', Auth::user()->id)
//                ->first();
//        }
//        dd($results);
        return view('quiniela/pointsXgame', compact('games', 'results', 'points'));
    }
}
