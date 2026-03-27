<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/crearUsuario', function (Request $request, UsuariosController $usuarioController) {
    try {
        $respuesta = $usuarioController->crear($request->nombre, $request->email);
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
