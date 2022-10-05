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
                'game.id'
            )
            ->orderBy('dateGame','asc')
            ->orderBy('timeGame', 'asc')
            ->get();


        $results = DB::table('quiniela as q')
            ->select('q.scoreTeam1', 'q.scoreTeam2', 'q.gameId')
            ->where('userId', '=', Auth::user()->id)
            ->get();


        return view('quiniela/index', compact('games', 'results'));
    }

    public function setQuiniela(){
        echo "edentro";
        $userId = Auth::user()->id;
        $gameId = request()->get('gameId');
        $score1 = request()->get('score1');
        $score2 = request()->get('score2');
        $timeGame = request()->get('timeGame');

        if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($timeGame)))){
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
            return back()->with('success', 'Actualizado correctamente');
        }else{
            return back()->with('error', 'Hora no válida');
        }



    }
}
