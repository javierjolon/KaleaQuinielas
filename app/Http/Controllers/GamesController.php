<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

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
            ->join('statusgame as sg', 'game.status', '=', 'sg.id')
            ->select(
                't1.name as team1',
                't2.name as team2',
                'game.dateGame',
                'game.timeGame',
                'game.id',
                'game.status',
                'game.score1',
                'game.score2',
                'sg.name as statusname'
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

        DB::table('game')
            ->where('id', '=', $gameId)
            ->update(['score1' => $score1, 'score2' => $score2]);

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

            $userPoint = DB::table('users')
                ->select('accumulatedPoints')
                ->where('id', '=', $quiniela->userId)
                ->first();

            DB::table('users')
                ->where('id', '=', $quiniela->userId)
                ->update(['accumulatedPointsTemp' => intval($pointsXGame + $userPoint->accumulatedPoints)]);

            $pointsXGame = 0;
        }

        $this->updateTempPosition();

        return redirect()->action('GamesController@initGame', ['gameId' => $gameId]);
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

    private function analizeGame($score1, $score2){
        if ($score1 > $score2){
            return 'G1';
        }elseif($score1 == $score2){
            return 'E';
        }elseif($score1 < $score2){
            return 'G2';
        }
    }

    public function initGame(){
        $gamesId = request()->get('gameId');

        $games = DB::table('game')
            ->join('team as t1', 't1.id', '=', 'game.team1')
            ->join('team as t2', 't2.id', '=', 'game.team2')
            ->join('statusgame as sg', 'game.status', '=', 'sg.id')
            ->select(
                't1.name as team1',
                't1.image as img1',
                't2.name as team2',
                't2.image as img2',
                'game.score1',
                'game.score2',
                'game.dateGame',
                'game.timeGame',
                'game.id',
                'game.status',
                'sg.name as statusname'
            )
            ->where('game.id', '=', $gamesId)
            ->orderBy('dateGame','asc')
            ->orderBy('timeGame', 'asc')
            ->first();

        return view("games/initGame", compact('games'));
    }

    public function endGame(){
        $gameId = request()->get('gameId');

        DB::table('game')
            ->where('id', '=', $gameId)
            ->update([ 'status' => 3 ]);

        $quinielas = DB::table('game')
            ->leftJoin('quiniela as q', 'game.id', '=' , 'q.gameId')
            ->select(
                'q.id as quinielaId',
                'game.score1 as final1',
                'game.score2 as final2',
                'q.scoreTeam1 as quiniela1',
                'q.scoreTeam2 as quiniela2',
                'q.userId'
            )
            ->where('game.id', '=', $gameId)
            ->get();


        $pointsXGame = 0;

        foreach($quinielas as $quiniela){
            if ($quiniela->final1 == $quiniela->quiniela1){
                $pointsXGame++;
            }
            if ($quiniela->final2 == $quiniela->quiniela2){
                $pointsXGame++;
            }

            $winMatch = $this->analizeGame($quiniela->final1, $quiniela->final2);
            $winQuiniela = $this->analizeGame($quiniela->quiniela1, $quiniela->quiniela2);

            if ($winMatch == $winQuiniela){
                $pointsXGame++;
            }



            DB::table('quiniela')
                ->where('id', '=', $quiniela->quinielaId)
                ->update([ 'pointsXGame' => $pointsXGame ]);

            $accumulatedPoints = DB::table('users')
                ->select('accumulatedPoints')
                ->where('id', '=', $quiniela->userId)->first();

            DB::table('users')
                ->where('id', '=', $quiniela->userId)
                ->update(['accumulatedPoints' => intval($pointsXGame + $accumulatedPoints->accumulatedPoints)]);

            $pointsXGame = 0;
        }
        $this->updateActualPostition();
        return back()->with('success', 'Actualizado correctamente');
    }

    public function starGame(){
        $gamesId = request()->get('gameId');

        DB::table('game')
            ->where('id', '=', $gamesId)
            ->update(['status' => 2]);

        return redirect()->action('GamesController@initGame', ['gameId' => $gamesId]);
    }
}
