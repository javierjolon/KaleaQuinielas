<?php

use App\Http\Controllers\GamesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuinielaController;
use App\Http\Controllers\VarController;
use App\Models\ReglaGrupo;
use App\Services\apiFotballService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['admin'])->group(function () {
    Route::get('/admin', function () {
        return Inertia::render('Admin/Dashboard');
    });

    Route::get('/admin/sync-api', function (apiFotballService $service) {
        $result = $service->sincronizarJugos('PD');
        return response()->json($result);
    });

    Route::get('/admin/recalcular-invalidos', function (GamesController $gamesController) {
        $juegoIds = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->where('qj.status', 'INVALID')
            ->whereNotNull('qj.quinielaEquipo1')
            ->whereNotNull('qj.quinielaEquipo2')
            ->whereIn('j.estatus', ['FINISHED', 'AWARDED'])
            ->select('qj.juegoId')
            ->distinct()
            ->pluck('juegoId');

        foreach ($juegoIds as $juegoId) {
            $gamesController->ApiActualizarPuntaje($juegoId, true);
        }

        return response()->json([
            'juegos_recalculados' => $juegoIds->count(),
            'ids' => $juegoIds,
        ]);
    });

    Route::get('/admin/recalcular-puntajes', function (GamesController $gamesController) {
        $juegoIds = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->whereIn('j.estatus', ['FINISHED', 'AWARDED'])
            ->whereNotIn('qj.status', ['FINISHED', 'INVALID'])
            ->select('qj.juegoId')
            ->distinct()
            ->pluck('juegoId');

        foreach ($juegoIds as $juegoId) {
            $gamesController->ApiActualizarPuntaje($juegoId, true);
        }

        return response()->json([
            'juegos_recalculados' => $juegoIds->count(),
            'ids' => $juegoIds,
        ]);
    });
});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $quinielas = DB::table('usuariosQuinielas as uq')
    ->join('quinielas as q', 'q.id', '=', 'uq.quinielaId')
    ->select('q.id', 'q.nombre', 'q.status', 'q.usuarioId')
    ->where('uq.usuarioId', '=', Auth::user()->id)
    ->orderBy('q.nombre')
    ->get();
    
    if (count($quinielas) === 0) {
        session([
            'quinielas' => [[
            'id' => 0,
            'nombre' => 'No estas en una quiniela, crea una nueva',
            'activo' => true
        ]]]);
    }else{
        $quinielasSessionActual = collect(session('quinielas', []));
        $quinielaActivaActual = $quinielasSessionActual->firstWhere('activo', true);
        $quinielaActivaId = $quinielaActivaActual['id'] ?? null;

        session([
            'quinielas' => $quinielas->values()->map(function ($q, $index) {
                return [
                    'id' => $q->id,
                    'nombre' => $q->nombre,
                    'activo' => false
                ];
            })->map(function ($q, $index) use ($quinielaActivaId) {
                if ($quinielaActivaId !== null) {
                    $q['activo'] = (int) $q['id'] === (int) $quinielaActivaId;
                } else {
                    $q['activo'] = $index === 0;
                }

                return $q;
            })
        ]);
    }

    $quinielaActiva = collect(session('quinielas', []))->firstWhere('activo', true);
    $usuariosQuiniela = collect();

    if (!empty($quinielaActiva) && (int) ($quinielaActiva['id'] ?? 0) > 0) {
        $qid = intval($quinielaActiva['id']);
        $usuariosQuiniela = DB::table('usuariosQuinielas as uq')
            ->join('users as u', 'u.id', '=', 'uq.usuarioId')
            ->select(
                'u.id', 'u.name', 'u.telefono', 'uq.subeBaja',
                DB::raw("(SELECT COALESCE(SUM(qj.puntosXjuego), 0) FROM quinielasJuegos qj INNER JOIN juegos j ON j.id = qj.juegoId WHERE qj.quinielaId = $qid AND qj.usuarioId = u.id AND qj.status = 'FINISHED' AND j.estatus = 'FINISHED') as puntosAcumulados")
            )
            ->where('uq.quinielaId', '=', $qid)
            ->orderByDesc('puntosAcumulados')
            ->orderBy('u.name')
            ->get();
    }

    return Inertia::render('Dashboard', [
        'quinielaActiva' => $quinielaActiva,
        'usuariosQuiniela' => $usuariosQuiniela,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/juegos', [GamesController::class, 'index'])->name('juegos.index');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/var', [VarController::class, 'index'])->name('var.index');
});

Route::get('/reglas', function () {
    $reglas = ReglaGrupo::where('activo', true)->orderBy('orden')->with(['items' => fn($q) => $q->orderBy('orden')])->get(['id', 'titulo']);
    return Inertia::render('Reglas/index', ['reglas' => $reglas]);
})->name('reglas.index');

Route::middleware('auth')->group(function () {
    Route::get('/quiniela', [QuinielaController::class, 'index'])->name('quiniela.index');
    Route::get('/quiniela/create', [QuinielaController::class, 'create'])->name('quiniela.create');
    Route::post('/quiniela', [QuinielaController::class, 'store'])->name('quiniela.store');
    Route::post('/quiniela/seleccionar-activa', [QuinielaController::class, 'seleccionarActiva'])->name('quiniela.seleccionar-activa');
    Route::post('/quiniela/agregar-usuario', [QuinielaController::class, 'agregarUsuario'])->name('quiniela.agregar-usuario');
    Route::delete('/quiniela/eliminar-usuario', [QuinielaController::class, 'eliminarUsuario'])->name('quiniela.eliminar-usuario');
    Route::patch('/quiniela/{juegoId}', [QuinielaController::class, 'patch'])->name('quiniela.patch');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::get('/quiniela', [QuinielaController::class, 'index'])->middleware(['auth', 'verified'])->name('quiniela');


require __DIR__.'/auth.php';
