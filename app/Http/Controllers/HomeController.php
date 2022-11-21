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
    public function var(){
        $respuesta = DB::table('game')
            ->select('users.name', 'quiniela.scoreTeam1', 'quiniela.scoreTeam2', 'quiniela.created_at as fecha', 't1.name as e1', 't2.name as e2', 'game.id')
            ->leftJoin('quiniela', 'game.id', '=', 'quiniela.gameId')
            ->leftJoin('team as t1', 'game.team1', '=', 't1.id')
            ->leftJoin('team as t2', 'game.team2', '=', 't2.id')
            ->leftJoin('users', 'quiniela.userId', '=', 'users.id')
            ->where('game.status', '!=', '1')
            ->orderBy('game.id', 'desc')
            ->get();

//        dd($respuesta);
        return view('User/var', compact('respuesta'));
    }


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
            $cantidad = DB::table('game')
                ->where('status', '=', 2)
                ->count('status');

//            if ($cantidad > 0){
//                $positions = DB::table('users')
//                    ->where('id', '<>', '1')
//                    ->orderBy('actualPositionTemp', 'asc')
//                    ->get();
//            }else{
                $positions = DB::table('users')
                    ->where('id', '<>', '1')
                    ->orderBy('actualPositionTemp', 'asc')
                    ->get();
//            }
//            dd($positions);

            return view('User/index', compact('positions', 'cantidad'));
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
                'password' => Hash::make($data['password']),
            ]);

            if ($data->file('image') != null){
                $file= $data->file('image');
                $filename= date('YmdHi').$file->getClientOriginalName();
                $file-> move(public_path('public/img/participantes'), $filename);
                $ruta = "public/public/img/participantes/".$filename;

                DB::table('users')
                    ->where('id', '=', $respuesta->id)
                    ->update(['image' => $ruta]);
            }

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

    public function tablaPosiciones(){

            $cantidad = DB::table('game')
                ->where('status', '=', 2)
                ->count('status');

            if ($cantidad > 0){
                $positions = DB::table('users')
                    ->where('id', '<>', '1')
                    ->orderBy('actualPositionTemp', 'asc')
                    ->get();
            }else{
                $positions = DB::table('users')
                    ->where('id', '<>', '1')
                    ->orderBy('actualPositionTemp', 'asc')
                    ->get();
            }
            return view('Admin/tablaPosiciones', compact('positions', 'cantidad'));

    }

}
