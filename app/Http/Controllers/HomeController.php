<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->email == 'admin@admin.com'){
            return view('Admin/index');
        }else{
            $positions = DB::table('users')
                ->where('id', '<>', '1')
                ->orderBy('actualPosition', 'asc')
                ->get();
            return view('User/index', compact('positions'));
        }
    }

    public function getPoints(){
        $points = DB::table('quiniela')->get();
    }

    public function registrar(){
        return view('Registrar.registrar');
    }

    public function registrarParticipante(Request $data){
        try {
            $respuesta = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);

            return back()->with('success', 'Creado correctamente');
        }catch (Exception $e){
            return back()->with('error', 'Error al crear usuario');
        }
    }

    public function porPoquito(){
        $fecha = date('Y-m-d');
        $games = DB::table('game as g')
            ->select('t1.name as team1', 't2.name as team2', 'u.name', 'q.scoreTeam1 as score1', 'q.scoreTeam2 as score2', 'g.score1 as original1', 'g.score2 as original2')
            ->leftJoin('quiniela as q', 'q.gameId', '=', 'g.id')
            ->leftJoin('users as u', 'u.id', '=', 'q.userId')
            ->leftJoin('team as t1', 't1.id', '=', 'g.team1')
            ->leftJoin('team as t2', 't2.id', '=', 'g.team2')
            ->where('g.dateGame', '=', $fecha)
            ->where('q.pointsXGame', '=', 2)
            ->get();

        return view('User/porPoquito', compact('games'));
    }

    public function nadaQueVer(){
        $fecha = date('Y-m-d');
        $games = DB::table('game as g')
            ->select('t1.name as team1', 't2.name as team2', 'u.name', 'q.scoreTeam1 as score1', 'q.scoreTeam2 as score2', 'g.score1 as original1', 'g.score2 as original2')
            ->leftJoin('quiniela as q', 'q.gameId', '=', 'g.id')
            ->leftJoin('users as u', 'u.id', '=', 'q.userId')
            ->leftJoin('team as t1', 't1.id', '=', 'g.team1')
            ->leftJoin('team as t2', 't2.id', '=', 'g.team2')
            ->where('g.dateGame', '=', $fecha)
            ->where('q.pointsXGame', '=', 0)
            ->get();

        return view('User/nadaQueVer', compact('games'));
    }

}
