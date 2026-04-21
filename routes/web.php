<?php

use App\Http\Controllers\GamesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuinielaController;
use App\Http\Controllers\VarController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                DB::raw("(SELECT COALESCE(SUM(puntosXjuego), 0) FROM quinielasJuegos WHERE quinielaId = $qid AND usuarioId = u.id AND status = 'FINISHED') as puntosAcumulados")
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

Route::middleware('auth')->group(function () {
    Route::get('/quiniela', [QuinielaController::class, 'index'])->name('quiniela.index');
    Route::get('/quiniela/create', [QuinielaController::class, 'create'])->name('quiniela.create');
    Route::post('/quiniela', [QuinielaController::class, 'store'])->name('quiniela.store');
    Route::post('/quiniela/seleccionar-activa', [QuinielaController::class, 'seleccionarActiva'])->name('quiniela.seleccionar-activa');
    Route::post('/quiniela/agregar-usuario', [QuinielaController::class, 'agregarUsuario'])->name('quiniela.agregar-usuario');
    Route::patch('/quiniela/{juegoId}', [QuinielaController::class, 'patch'])->name('quiniela.patch');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::get('/quiniela', [QuinielaController::class, 'index'])->middleware(['auth', 'verified'])->name('quiniela');


require __DIR__.'/auth.php';
