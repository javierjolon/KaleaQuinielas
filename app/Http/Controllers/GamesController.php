<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GamesController extends Controller
{
    public function index(){
        $teams = DB::table('team')
            ->orderBy('name', 'asc')
            ->get();
        $types = DB::table('type_game')->get();
        $games = DB::table('game')
            ->join('team as t1', 't1.id', '=', 'game.team1')
            ->join('team as t2', 't2.id', '=', 'game.team2')
            ->join('type_game as tg', 'tg.id', '=', 'game.typeGame')
            ->select('t1.name as team1', 't2.name as team2', 'game.dateGame', 'game.timeGame', 'tg.name as type', 't1.image as image1', 't2.image as image2')
            ->orderBy('dateGame','asc')
            ->orderBy('timeGame', 'asc')
            ->get();

        return view('games/index', compact('teams', 'games', 'types'));
    }

    public function setGame()
    {
        $team1 = request()->get('team1');
        $team2 = request()->get('team2');
        $date = request()->get('date');
        $time = request()->get('time');
        $type = request()->get('type');

        DB::table('game')->insert(
            [
                'team1' => $team1,
                'score1' => null,
                'team2' => $team2,
                'score2' => null,
                'typeGame' => $type,
                'dateGame' => $date,
                'timeGame' => $time,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );

        return back();
    }

    public function addResult(){
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

        return view('games/addResult', compact('games'));
    }

    public function setResultGame(){
        $gameId = request()->get('gameId');
        $score1 = request()->get('score1');
        $score2 = request()->get('score2');
        $marcadorFinal = request()->get('marcadorFinal');

        DB::table('game')
            ->updateOrInsert(
                ['id' => $gameId],
                ['score1' => $score1, 'score2' => $score2]
            );

        $quinielas = DB::table('quiniela')
            ->where('gameId', '=', $gameId)
            ->get();

        $pointsXGame = 0;
        foreach($quinielas as $quiniela){
            if ($score1 == $quiniela->scoreTeam1){
                $pointsXGame++;
            }
            if ($score2 == $quiniela->scoreTeam2){
                $pointsXGame++;
            }

            $winMatch = $this->analizeGame($score1, $score2);
            $winQuiniela = $this->analizeGame($quiniela->scoreTeam1, $quiniela->scoreTeam2);

            if ($winMatch == $winQuiniela){
                $pointsXGame++;
            }

            DB::table('quiniela')
                ->where('id', '=', $quiniela->id)
                ->update([ 'pointsXGame' => $pointsXGame ]);

            $accumulatedPoints = DB::table('users')
                ->select('points')
                ->where('id', '=', $quiniela->userId)->first();

            DB::table('users')
                ->where('id', '=', $quiniela->userId)
                ->update([
                    'points' => intval($pointsXGame + $accumulatedPoints->points)
                ]);

            $pointsXGame = 0;
        }
        $this->updatePostition();
        return back()->with('success', 'Actualizado correctamente');
    }

    private function updatePostition(){

        $users = DB::table('users')
            ->where('id', '<>', '1')
            ->orderBy('points', 'desc')
            ->get();

        foreach ($users as $clave => $user) {
            if ($user->actualPosition > $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPosition' => intval($clave +1), 'posicion' => 's']);
            }elseif ($user->actualPosition == $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPosition' => intval($clave +1), 'posicion' => 'i']);
            }elseif($user->actualPosition < $clave+1){
                DB::table('users')
                    ->where('id', '=', $user->id)
                    ->update(['actualPosition' => intval($clave +1), 'posicion' => 'b']);
            }
        }
    }


    private function analizeGame($score1, $score2){
        if ($score1 > $score2){
            return 'G1';
        }elseif($score1 == $score2){
            return 'E';
        }elseif($score1 < $score2){
            return 'G2';
        }
    }
}
