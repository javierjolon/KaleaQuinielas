<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\apiFotballService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use function PHPSTORM_META\map;

class QuinielaController extends Controller
{

    public function create() {
        $quinielasActivas = DB::table('quinielas')
            ->select('id', 'nombre')
            ->where('usuarioId', '=', Auth::id())
            ->where('status', '=', 'TIMED')
            ->orderBy('nombre')
            ->get();

        $competicionesDisponibles = DB::table('juegos')
            ->select('competicion', 'season', DB::raw('MAX(nombreCompeticion) as nombreCompeticion'))
            ->where('estatus', '!=', 'FINISHED')
            ->whereNotNull('competicion')
            ->whereNotNull('season')
            ->groupBy('competicion', 'season')
            ->orderByDesc('season')
            ->orderBy('nombreCompeticion')
            ->get()
            ->map(function ($item) {
                return [
                    'competicion' => $item->competicion,
                    'season' => $item->season,
                    'nombre' => $item->nombreCompeticion ?: $item->competicion,
                ];
            });

        return Inertia::render("Quiniela/create", [
            'quinielasActivas' => $quinielasActivas,
            'competicionesDisponibles' => $competicionesDisponibles,
            'estatus' => session('estatus'),
        ]);
    }

    public function store(Request $request){
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'competicion' => ['required', 'string'],
            'season' => ['required', 'integer'],
        ]);

        // Registra la quiniela en la base de datos
        $quinielaId = DB::table("quinielas")
            ->insertGetId([
                'usuarioId' => Auth::user()->id,
                'nombre' => $data['nombre'],
                'status' => "TIMED",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        // Toma los partidos de la competicion y season seleccionadas.
        $partidos = DB::table("juegos")
            ->where('competicion', '=', $data['competicion'])
            ->where('season', '=', $data['season'])
            ->get();

        if ($partidos->isEmpty()) {
            return back()->withErrors([
                'competicion' => 'No hay juegos disponibles para la competicion seleccionada.',
            ]);
        }

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

        return back()->with('estatus', 'Quiniela creada correctamente.');
    }

    public function agregarUsuario(Request $request)
    {
        $data = $request->validate([
            'telefono' => ['required', 'string', 'regex:/^[0-9+\s\-]{8,20}$/'],
            'quinielaId' => ['required', 'integer'],
        ]);

        $quiniela = DB::table('quinielas')
            ->where('id', '=', $data['quinielaId'])
            ->where('usuarioId', '=', Auth::id())
            ->where('status', '=', 'TIMED')
            ->first();

        if (! $quiniela) {
            return back()->withErrors([
                'telefonoInvitado' => 'La quiniela seleccionada no es valida o no esta activa.',
            ]);
        }

        $usuarioInvitado = DB::table('users')
            ->select('id', 'telefono')
            ->where('telefono', '=', $data['telefono'])
            ->first();

        if (! $usuarioInvitado) {
            return back()->withErrors([
                'telefonoInvitado' => 'No existe un usuario con ese numero de telefono.',
            ]);
        }

        $yaExiste = DB::table('usuariosQuinielas')
            ->where('usuarioId', '=', $usuarioInvitado->id)
            ->where('quinielaId', '=', $quiniela->id)
            ->exists();

        if ($yaExiste) {
            return back()->withErrors([
                'telefonoInvitado' => 'Ese usuario ya pertenece a la quiniela seleccionada.',
            ]);
        }

        DB::table('usuariosQuinielas')->insert([
            'usuarioId' => $usuarioInvitado->id,
            'quinielaId' => $quiniela->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filtroCompeticionSeason = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->select('j.competicion', 'j.season')
            ->where('qj.quinielaId', '=', $quiniela->id)
            ->groupBy('j.competicion', 'j.season')
            ->get();

        $partidosQuery = DB::table('juegos')->select('id');

        if ($filtroCompeticionSeason->count() === 1) {
            $partidosQuery
                ->where('competicion', '=', $filtroCompeticionSeason[0]->competicion)
                ->where('season', '=', $filtroCompeticionSeason[0]->season);
        }

        $partidos = $partidosQuery->get();
        $partidosUsuario = [];

        foreach ($partidos as $partido) {
            $partidosUsuario[] = [
                'quinielaId' => $quiniela->id,
                'usuarioId' => $usuarioInvitado->id,
                'juegoId' => $partido->id,
                'puntosXjuego' => 0,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($partidosUsuario)) {
            DB::table('quinielasJuegos')->insert($partidosUsuario);
        }

        return back()->with('estatus', 'Usuario agregado correctamente a la quiniela.');
    }

    public function seleccionarActiva(Request $request)
    {
        $data = $request->validate([
            'quinielaId' => ['required', 'integer'],
        ]);

        $existe = DB::table('usuariosQuinielas as uq')
            ->join('quinielas as q', 'q.id', '=', 'uq.quinielaId')
            ->where('uq.usuarioId', '=', Auth::id())
            ->where('q.id', '=', $data['quinielaId'])
            ->where('q.status', '=', 'TIMED')
            ->exists();

        if (! $existe) {
            return back()->withErrors([
                'quinielaId' => 'La quiniela seleccionada no es valida.',
            ]);
        }

        $quinielasSession = collect(session('quinielas', []));

        if ($quinielasSession->isEmpty()) {
            $quinielasUsuario = DB::table('usuariosQuinielas as uq')
                ->join('quinielas as q', 'q.id', '=', 'uq.quinielaId')
                ->select('q.id', 'q.nombre')
                ->where('uq.usuarioId', '=', Auth::id())
                ->where('q.status', '=', 'TIMED')
                ->orderBy('q.nombre')
                ->get();

            $quinielasSession = $quinielasUsuario->map(function ($q) use ($data) {
                return [
                    'id' => $q->id,
                    'nombre' => $q->nombre,
                    'activo' => (int) $q->id === (int) $data['quinielaId'],
                ];
            });
        } else {
            $quinielasSession = $quinielasSession->map(function ($q) use ($data) {
                $q['activo'] = (int) $q['id'] === (int) $data['quinielaId'];
                return $q;
            });
        }

        session([
            'quinielas' => $quinielasSession->values()->all(),
        ]);

        return back()->with('estatus', 'Quiniela activa actualizada.');
    }


    public function index(){
        $usuarioId = Auth::user()->id;
        $quinielaActivaId = 0;

        $quinielaActiva = collect(session('quinielas', []))->firstWhere('activo', true);
        $quinielaActivaId = (int) ($quinielaActiva['id'] ?? 0);

        if ($quinielaActivaId <= 0) {
            return Inertia::render('Quiniela/quiniela', [
                'juegosPendientes' => [],
                'juegosIngresados' => [],
                'juegosFinalizados' => [],
                'quinielaActiva' => null,
            ]);
        }

        $juegosPendientes = DB::table('quinielasJuegos as qj')
        ->select(
            'qj.quinielaEquipo1', 'qj.quinielaEquipo2', 'qj.status as estatusQuiniela', 'qj.juegoId as id',
            'juegos.equipo1', 'juegos.equipo2', 'juegos.estatus as estatusJuego', 'juegos.imagenEquipo1', 'juegos.imagenEquipo2', 'juegos.ronda', 'juegos.fechaJuego', 'juegos.horaJuego', 'juegos.resultadoEquipo1', 'juegos.resultadoEquipo2')
        ->leftJoin('juegos', 'qj.juegoId', 'juegos.id')
        ->where('usuarioId', "=", $usuarioId)
        ->where("quinielaId", "=", $quinielaActivaId)
        ->whereDate('juegos.fechaJuego', '>=', Carbon::today()->toDateString())
        ->whereNull('qj.quinielaEquipo1')
        ->whereNull('qj.quinielaEquipo2')
        ->whereNotIn('juegos.estatus', ['FINISHED', 'AWARDED', 'CANCELLED', 'POSTPONED', 'SUSPENDED'])
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

        $listadoJuegosPendientes = $juegosPendientes->groupBy(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->format('d-m-Y');
        });


        
        // dd($listadoJuegosPendientes);

        $juegosIngresados = DB::table('quinielasJuegos as qj')
        ->select(
            'qj.quinielaEquipo1', 'qj.quinielaEquipo2', 'qj.status as estatusQuiniela', 'qj.juegoId as id', 'qj.puntosXjuego',
            'juegos.equipo1', 'juegos.equipo2', 'juegos.imagenEquipo1', 'juegos.imagenEquipo2', 'juegos.ronda', 'juegos.fechaJuego', 'juegos.horaJuego', 'juegos.estatus as estatusJuego', 'juegos.resultadoEquipo1', 'juegos.resultadoEquipo2')
        ->leftJoin('juegos', 'qj.juegoId', 'juegos.id')
        ->where('usuarioId', "=", $usuarioId)
        ->where("qj.quinielaId", "=", $quinielaActivaId)
        ->whereDate('juegos.fechaJuego', '>=', Carbon::today()->toDateString())
        ->whereNotNull('qj.quinielaEquipo1')
        ->whereNotNull('qj.quinielaEquipo2')
        ->where(function($q) {
            $q->whereNotIn('qj.status', ['FINISHED', 'INVALID'])
              ->orWhereIn('juegos.estatus', ['IN_PLAY', 'LIVE', 'PAUSED']);
        })
        ->whereNotIn('juegos.estatus', ['FINISHED', 'AWARDED', 'CANCELLED', 'POSTPONED', 'SUSPENDED'])
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

        $listadoJuegosIngresados = $juegosIngresados->groupBy(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->format('d-m-Y');
        });

        // dd($listadoJuegosIngresados);


        $juegosFinalizados = DB::table('quinielasJuegos as qj')
        ->select(
            'qj.quinielaEquipo1', 'qj.quinielaEquipo2', 'qj.status as estatusQuiniela', 'qj.juegoId as id', 'qj.puntosXjuego',
            'juegos.equipo1', 'juegos.equipo2', 'juegos.imagenEquipo1', 'juegos.imagenEquipo2', 'juegos.ronda', 'juegos.fechaJuego', 'juegos.horaJuego', 'juegos.estatus as estatusJuego', 'juegos.resultadoEquipo1', 'juegos.resultadoEquipo2')
        ->leftJoin('juegos', 'qj.juegoId', 'juegos.id')
        ->where('usuarioId', "=", $usuarioId)
        ->where("qj.quinielaId", "=", $quinielaActivaId)
        ->whereIn('qj.status', ['FINISHED', 'INVALID'])
        ->whereNotIn('juegos.estatus', ['IN_PLAY', 'LIVE', 'PAUSED'])
        ->orderByDesc('juegos.fechaJuego')
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

        $listadoJuegosFinalizados = $juegosFinalizados->groupBy(function ($juego) {
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
        $quinielaActivaId = (int) ($quinielaActiva['id'] ?? 0);

        if ($quinielaActivaId <= 0) {
            return back()->withErrors(['error' => 'No hay una quiniela activa seleccionada.']);
        }

        
        $data = [ 'updated_at' => now() ];
    
        if (!is_null($quinielaEquipo1)) {
            $data['quinielaEquipo1'] = $quinielaEquipo1;
        }
    
        if (!is_null($quinielaEquipo2)) {
            $data['quinielaEquipo2'] = $quinielaEquipo2;
        }

        $datosJuego = DB::table("juegos")
        ->select("fechaJuego", "horaJuego", "estatus")    
        ->where("id", "=", $juegoId)
        ->first();

        if (! $datosJuego) {
            return back()->withErrors(['error' => 'El partido no existe.']);
        }

        $registroQuiniela = DB::table('quinielasJuegos')
            ->select('quinielaEquipo1', 'quinielaEquipo2')
            ->where('usuarioId', '=', Auth::id())
            ->where('quinielaId', '=', $quinielaActivaId)
            ->where('juegoId', '=', $juegoId)
            ->first();

        // Regla: si el partido ya no esta TIMED, no se permite capturar/modificar quiniela.
        if ($datosJuego->estatus !== 'TIMED') {
            $sinCaptura = is_null($registroQuiniela?->quinielaEquipo1) && is_null($registroQuiniela?->quinielaEquipo2);

            if ($sinCaptura) {
                $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActivaId, [
                    'status' => 'INVALID',
                    'updated_at' => now(),
                ]);
            }

            return back()->withErrors(['error' => 'El partido ya no esta programado. No se puede ingresar quiniela.']);
        }

        $minutesCierre = (int) Configuracion::get('minutos_cierre_quiniela', 10);
        $cierreCaptura = Carbon::parse($datosJuego->fechaJuego . ' ' . $datosJuego->horaJuego)->subMinutes($minutesCierre);
        $fueraDeHorario = Carbon::now()->greaterThanOrEqualTo($cierreCaptura);

        if ($fueraDeHorario) {
            $sinCaptura = is_null($registroQuiniela?->quinielaEquipo1) && is_null($registroQuiniela?->quinielaEquipo2);

            if ($sinCaptura) {
                $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActivaId, [
                    'status' => 'INVALID',
                    'updated_at' => now(),
                ]);

                return back()->withErrors(['error' => 'Fuera de horario. El partido se marco como INVALID.']);
            }

            return back()->withErrors(['error' => 'Fuera de horario. Ya no es posible modificar este partido.']);
        }

        if(Carbon::today()->lt($datosJuego->fechaJuego)){
            $data['status'] = 'TIMED';
            $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActivaId, $data);
            return back();
            
        }elseif (Carbon::today()->eq($datosJuego->fechaJuego)){
            if (! $fueraDeHorario){
                $data['status'] = 'TIMED';
                $this->actualizarDB('quinielasJuegos', $juegoId, $quinielaActivaId, $data);
                return back();
            }else{
                return back()->withErrors(['error' => 'Fuera de horario.']);
            }
        }else{
            return back()->withErrors(['error' => 'Fuera de horario.']);
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
