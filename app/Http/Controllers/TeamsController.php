<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\Promise\all;

class TeamsController extends Controller
{
    public function index()
    {
        $teams = DB::table('teams')->orderBy('group', 'asc') ->get();
        return view('teams/index', compact('teams'));
    }

    public function setTeam()
    {
        $team = request()->get('team');
        $group = request()->get('group');
        DB::table('teams')->insert([
            [
                'name' => $team,
                'group' => $group,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ],
        ]);
        return back();
    }
}
