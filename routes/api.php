<?php

use App\Http\Controllers\Api\MobileController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\UsuariosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ── Mobile API (Sanctum token auth) ──────────────────────────────────────────
Route::post('/login',    [MobileController::class, 'login']);
Route::post('/register', [MobileController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',    [MobileController::class, 'user']);
    Route::post('/logout', [MobileController::class, 'logout']);

    Route::get('/dashboard',          [MobileController::class, 'dashboard']);
    Route::get('/quiniela',           [MobileController::class, 'quiniela']);
    Route::patch('/quiniela/{juegoId}', [MobileController::class, 'patchPrediccion']);
    Route::post('/quiniela',          [MobileController::class, 'crearQuiniela']);

    Route::post('/quiniela/agregar-usuario',  [MobileController::class, 'agregarUsuario']);
    Route::delete('/quiniela/eliminar-usuario', [MobileController::class, 'eliminarUsuario']);

    Route::post('/partidos-equipo', [MobileController::class, 'partidosEquipo']);
    Route::get('/tabla-liga',       [MobileController::class, 'tablaLiga']);
});

// ── Legacy web-facing API ─────────────────────────────────────────────────────
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/crearUsuario', function (Request $request, UsuariosController $usuarioController) {
    try {
        $respuesta = $usuarioController->crear($request->nombre, $request->telefono);
        return response()->json([
            'codigo' => 1,
            'mensaje' => $respuesta
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'codigo' => 0,
            'mensaje' => $th->getMessage()
        ]);
    }
});

Route::post('/actualizarPartido', function(Request $request, GamesController $juegoController){
    try {
        $respuesta = $juegoController->ApiActualizarPartido($request->partidoId, $request->resultadoEquipo1, $request->resultadoEquipo2, $request->estatus);
        $respuesta = $juegoController->ApiActualizarPuntaje($request->partidoId, $request->resultadoEquipo1, $request->resultadoEquipo2, $request->estatus);
        return response()->json([
            'codigo' => 1,
            'mensaje' => $respuesta
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'codigo' => 0,
            'mensaje' => $th->getMessage()
        ]);
    }
});
