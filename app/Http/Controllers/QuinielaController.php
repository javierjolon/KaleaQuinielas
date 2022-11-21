<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuinielaController extends Controller
{
    public function index(){

        $games = DB::table('game')
            ->join('team as t1', 't1.id', '=', 'game.team1')
            ->join('team as t2', 't2.id', '=', 'game.team2')
            ->select(
                't1.name as team1',
                't2.name as team2',
                'game.score1',
                'game.score2',
                'game.dateGame',
                'game.timeGame',
                'game.status',
                'game.id'
            )
            ->orderBy('dateGame','asc')
            ->orderBy('timeGame', 'asc')
            ->get();


        $results = DB::table('quiniela')
            ->where('userId', '=', Auth::user()->id)
            ->get();

        return view('quiniela/index', compact('games', 'results'));
    }

    public function setQuiniela(){
        $userId = Auth::user()->id;
        $gameId = request()->get('gameId');
        $score1 = request()->get('score1');
        $score2 = request()->get('score2');
        $timeGame = request()->get('timeGame');
        $dateGame = request()->get('dateGame');

        if($dateGame > date('Y-m-d')){
            $this->updateQuiniela($userId, $gameId, $score1, $score2);
            return back()->with('success', 'Actualizado correctamente');
        }elseif ($dateGame = date('Y-m-d')){
            if (date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($timeGame)))){
                $this->updateQuiniela($userId, $gameId, $score1, $score2);
                return back()->with('success', 'Actualizado correctamente');
            }else{
                return back()->with('error', 'Hora no válida');
            }
        }
    }

    public function updateQuiniela($userId, $gameId, $score1, $score2){
        DB::table('quiniela')
            ->updateOrInsert(
                ['userId'=> $userId, 'gameId' => $gameId],
                [
                    'userId' => $userId,
                    'gameId' => $gameId,
                    'scoreTeam1' => $score1,
                    'scoreTeam2' => $score2,
                    'pointsXGame' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
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
