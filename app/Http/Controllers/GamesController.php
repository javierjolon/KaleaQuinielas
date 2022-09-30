<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GamesController extends Controller
{
    public function index(){
        $teams = DB::table('teams')->get();
        $games = DB::table('games')
            ->orderBy('dateGame','asc')
            ->orderBy('timeGame', 'asc')
            ->get();

        return view('games/index', compact('teams', 'games'));
    }

    public function setGame()
    {
        $team1 = request()->get('team1');
        $team2 = request()->get('team2');
        $date = request()->get('date');
        $time = request()->get('time');

        DB::table('games')->insert(
            [
                'team1' => $team1,
                'score1' => null,
                'team2' => $team2,
                'score2' => null,
                'dateGame' => $date,
                'timeGame' => $time,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        );

        return back();
    }
}
