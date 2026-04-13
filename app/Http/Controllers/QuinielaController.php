<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\apiFotballService;
use Carbon\Carbon;
use Inertia\Inertia;

use function PHPSTORM_META\map;

class QuinielaController extends Controller
{

    public function create() {
        return Inertia::render("Quiniela/create", []);
    }

    public function store(){
        $nombreQuiniela = request()->get('nombre');

        // Registra la quiniela en la base de datos
        $quinielaId = DB::table("quinielas")
            ->insertGetId([
                'usuarioId' => Auth::user()->id,
                'nombre' => $nombreQuiniela,
                'status' => "TIMED",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        // Toma todos los partidos de la tabla juegos y los en la tabla quinielaJuegos con el nuevo usuario 
        $partidos = DB::table("juegos")->get();
        $data = [];
        
        foreach ($partidos as $partido) {
            $data[] = [
                'quinielaId' => $quinielaId,
                'usuarioId' => Auth::user()->id,
                'juegoId' => $partido->id,
                'puntosXjuego' => 0,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('quinielasJuegos')->insert($data);

        // asocia el usuario creado a la quiniela creada
        DB::table('usuariosQuinielas')->insert([
            'usuarioId' => Auth::user()->id,
            'quinielaId' => $quinielaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }


    public function index(){
        $usuarioId = Auth::user()->id;
        $quinielaActivaId = 0;

        $quinielaActiva = collect(session('quinielas'))->firstWhere('activo', true);
        $quinielaActivaId = $quinielaActiva['id']; 

        $juegosPendientes = DB::table('quinielasJuegos as qj')
        ->select(
            'qj.quinielaEquipo1', 'qj.quinielaEquipo2', 'qj.status as estatusQuiniela', 'qj.juegoId as id', 
            'juegos.equipo1', 'juegos.equipo2', 'juegos.estatus as estatusJuego', 'juegos.imagenEquipo1', 'juegos.imagenEquipo2', 'juegos.ronda', 'juegos.fechaJuego', 'juegos.horaJuego')
        ->leftJoin('juegos', 'qj.juegoId', 'juegos.id')
        ->where('usuarioId', "=", $usuarioId)
        ->where("quinielaId", "=", $quinielaActivaId)
        ->where('qj.status', "=", "PENDING")    
        ->orderBy('juegos.fechaJuego')
        ->orderBy('juegos.horaJuego')    
        ->get()
        ->map(function ($game) {
            $game->equipo1 = traducir_equipos($game->equipo1 ?? "Pendiente");
            $game->equipo2 = traducir_equipos($game->equipo2 ?? "Pendiente");
            $game->estatusQuiniela = ['nombre' => traducir_estatus($game->estatusQuiniela ?? " "), 'color' => color_estatus($game->estatusQuiniela ?? " ")];
            $game->estatusJuego = ['nombre' => traducir_estatus($game->estatusJuego ?? " "), 'color' => color_estatus($game->estatusJuego ?? " ")];
            $game->tipoJuego = traducir_rondas($game->ronda ?? " ");
            return $game;
        });

        $listadoJuegosPendientes = $juegosPendientes->filter(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->isToday() || Carbon::parse($juego->fechaJuego)->isFuture();
        })->groupBy(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->format('d-m-Y');
        });


        
        // dd($listadoJuegosPendientes);

        $juegosIngresados = DB::table('quinielasJuegos as qj')
        ->select(
            'qj.quinielaEquipo1', 'qj.quinielaEquipo2', 'qj.status as estatusQuiniela', 'qj.juegoId as id',
            'juegos.equipo1', 'juegos.equipo2', 'juegos.imagenEquipo1', 'juegos.imagenEquipo2', 'juegos.ronda', 'juegos.fechaJuego', 'juegos.horaJuego', 'juegos.estatus as estatusJuego')
        ->leftJoin('juegos', 'qj.juegoId', 'juegos.id')
        ->where('usuarioId', "=", $usuarioId)
        ->where("qj.quinielaId", "=", $quinielaActivaId)
        ->where('qj.status', "=", "LOCKED")    
        ->orWhere('qj.status', "=", "TIMED")    
        ->orderBy('juegos.fechaJuego')
        ->orderBy('juegos.horaJuego')    
        ->get()
        ->map(function ($game) {
            $game->equipo1 = traducir_equipos($game->equipo1 ?? "Pendiente");
            $game->equipo2 = traducir_equipos($game->equipo2 ?? "Pendiente");
            $game->estatusQuiniela = ['nombre' => traducir_estatus($game->estatusQuiniela ?? " "), 'color' => color_estatus($game->estatusQuiniela ?? " ")];
            $game->estatusJuego = ['nombre' => traducir_estatus($game->estatusJuego ?? " "), 'color' => color_estatus($game->estatusJuego ?? " ")];
            $game->tipoJuego = traducir_rondas($game->ronda ?? " ");
            return $game;
        });

        // dd($juegosIngresados);

        $listadoJuegosIngresados = $juegosIngresados->filter(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->isToday() || Carbon::parse($juego->fechaJuego)->isFuture();
        })->groupBy(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->format('d-m-Y');
        });

        // dd($listadoJuegosIngresados);


        $juegosFinalizados = DB::table('quinielasJuegos as qj')
        ->select(
            'qj.quinielaEquipo1', 'qj.quinielaEquipo2', 'qj.status as estatusQuiniela', 'qj.juegoId as id',
            'juegos.equipo1', 'juegos.equipo2', 'juegos.imagenEquipo1', 'juegos.imagenEquipo2', 'juegos.ronda', 'juegos.fechaJuego', 'juegos.horaJuego', 'juegos.estatus as estatusJuego')
        ->leftJoin('juegos', 'qj.juegoId', 'juegos.id')
        ->where('usuarioId', "=", $usuarioId)
        ->where("qj.quinielaId", "=", $quinielaActivaId)
        ->where('qj.status', "=", "FINISHED")    
        ->orWhere('qj.status', "=", "INVALID")    
        ->orderBy('juegos.fechaJuego')
        ->orderBy('juegos.horaJuego')    
        ->get()
        ->map(function ($game) {
            $game->equipo1 = traducir_equipos($game->equipo1 ?? "Pendiente");
            $game->equipo2 = traducir_equipos($game->equipo2 ?? "Pendiente");
            $game->estatusQuiniela = ['nombre' => traducir_estatus($game->estatusQuiniela ?? " "), 'color' => color_estatus($game->estatusQuiniela ?? " ")];
            $game->estatusJuego = ['nombre' => traducir_estatus($game->estatusJuego ?? " "), 'color' => color_estatus($game->estatusJuego ?? " ")];
            $game->tipoJuego = traducir_rondas($game->ronda ?? " ");
            return $game;
        });

        // dd($juegosFinalizados);

        $listadoJuegosFinalizados = $juegosFinalizados->filter(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->isLastDay();
        })->groupBy(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->format('d-m-Y');
        });

        return Inertia::render('Quiniela/quiniela', ['juegosPendientes' => $listadoJuegosPendientes, 'juegosIngresados' => $listadoJuegosIngresados, 'juegosFinalizados' => $listadoJuegosFinalizados, 'quinielaActiva' => $quinielaActiva]);
    }

    // public function store(){
    //     $juegoId = request()->get('juego_id');
    //     $equipo1 = request()->get('equipo1');
    //     $equipo2 = request()->get('equipo2');

    //     $datosJuego = DB::table("game")
    //     ->select("fechaJuego", "horaJuego")    
    //     ->where("id", "=", $juegoId)
    //     ->first();

    //     $horaJuego = Carbon::parse($datosJuego->horaJuego)->subMinutes(10);

    //     if(Carbon::today()->lt($datosJuego->fechaJuego) && $datosJuego->fechaJuego == "TIMED"){
    //         DB::table('quiniela')
    //             ->Insert(
    //             [
    //                 'usuarioId' => Auth::user()->id,
    //                 'juegoId' => $juegoId,
    //                 'quinielaEquipo1' => $equipo1,
    //                 'quinielaEquipo2' => $equipo2,
    //                 'puntosXjuego' => 0,
    //                 'estatus' => "TIMED",
    //                 'created_at' => date('Y-m-d H:i:s'),
    //                 'updated_at' => date('Y-m-d H:i:s')
    //             ]);
    //         return back();
    //     }elseif (Carbon::today()->eq($datosJuego->fechaJuego) && $datosJuego->fechaJuego == "TIMED"){
    //         if (Carbon::now()->lt($horaJuego)){
    //             DB::table('quiniela')
    //             ->Insert(
    //             [
    //                 'usuarioId' => Auth::user()->id,
    //                 'juegoId' => $juegoId,
    //                 'quinielaEquipo1' => $equipo1,
    //                 'quinielaEquipo2' => $equipo2,
    //                 'puntosXjuego' => 0,
    //                 'estatus' => "TIMED",
    //                 'created_at' => date('Y-m-d H:i:s'),
    //                 'updated_at' => date('Y-m-d H:i:s')
    //             ]);
    //             return back();
    //         }else{
    //             $array = [ 'estatus' => 'INVALID', 'quinielaEquipo1' => '-1', 'quinielaEquipo2' => '-1', 'puntosXjuego' => 0];
    //             $this->actualizarDB('quiniela', $juegoId, $array);
    //             return back()->withErrors(['error' => 'Fuera de horario']);
    //         }
    //     }else{
    //         $array = [ 'estatus' => 'INVALID', 'quinielaEquipo1' => '-1', 'quinielaEquipo2' => '-1', 'puntosXjuego' => 0];
    //         $this->actualizarDB('quiniela', $juegoId, $array);
    //         return back()->withErrors(['error' => 'Fuera de horario']);
    //     }
    // }

    public function patch($juegoId){
        $quinielaEquipo1 = request()->get("quinielaEquipo1");
        $quinielaEquipo2 = request()->get("quinielaEquipo2");
        $quinielaActiva = collect(session('quinielas'))->firstWhere('activo', true);

        
        $data = [ 'updated_at' => now() ];
    
        if (!is_null($quinielaEquipo1)) {
            $data['quinielaEquipo1'] = $quinielaEquipo1;
        }
    
        if (!is_null($quinielaEquipo2)) {
            $data['quinielaEquipo2'] = $quinielaEquipo2;
        }

        $datosJuego = DB::table("juegos")
        ->select("fechaJuego", "horaJuego")    
        ->where("id", "=", $juegoId)
        ->first();

        $horaJuego = Carbon::parse($datosJuego->horaJuego)->subMinutes(10);

        if(Carbon::today()->lt($datosJuego->fechaJuego)){
            $data['status'] = 'TIMED';
            $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActiva, $data);
            return back();
            
        }elseif (Carbon::today()->eq($datosJuego->fechaJuego)){
            
            if (Carbon::now()->lt($horaJuego)){
                $data['status'] = 'TIMED';
                $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActiva, $data);
                return back();
            }else{
                $data['status'] = 'LOCKED';
                $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActiva, $data);
                return back()->withErrors(['error' => 'Fuera de horario']);
            }
        }else{
            $data['status'] = 'LOCKED';
            $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActiva, $data);
            return back()->withErrors(['error' => 'Fuera de horario']);
        }

        return back();
    }

    private function actualizarDB($tabla, $juegoId, $quinielaId, $arrayCampos){
        DB::table($tabla)
        ->updateOrInsert(
            [
                'usuarioId'=> Auth::user()->id, 
                'juegoId' => $juegoId,
                'quinielaId' => $quinielaId,
            ],
            $arrayCampos
        );
        return;
    }

    public function puntosXjuego(){
        $games = DB::table('game')
            ->join('team as t1', 't1.id', '=', 'game.equipo1')
            ->join('team as t2', 't2.id', '=', 'game.equipo2')
            ->join('estatusgame as sg', 'game.estatus', '=', 'sg.id')
            ->select(
                't1.name as equipo1',
                't2.name as equipo2',
                'game.resultadoEquipo1',
                'game.resultadoEquipo2',
                'game.fechaJuego',
                'game.horaJuego',
                'game.id',
                'game.estatus',
                'sg.name as nameestatusGame',
                't1.image as image1',
                't2.image as image2'
            )
            ->orderBy('fechaJuego','asc')
            ->orderBy('horaJuego', 'asc')
            ->get();

        $results = DB::table('quiniela')
            ->where('usuarioId', '=', Auth::user()->id)
            ->get();

//        $respuesta = DB::table('game')
//                ->where('estatus', '=', 2)
//                ->count('estatus') > 0;

        $points = DB::table('quiniela')
            ->where('usuarioId', '=', Auth::user()->id)
            ->sum('puntosXjuego');

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
        return view('quiniela/puntosXjuego', compact('games', 'results', 'points'));
    }
}
