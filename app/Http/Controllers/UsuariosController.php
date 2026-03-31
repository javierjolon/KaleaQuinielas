<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UsuariosController extends Controller
{
    public function crear($nombre, $email){
        try {
            DB::table("users")
            ->insert([
                "name" => $nombre,
                "email" => $email,
                "posicionActual" => 0,
                "posicionActualTemp" => 0,
                "puntosAcumulados" => 0,
                "puntosAcumuladosTemp" => 0,
                "subeBaja" => "s",
                "subeBajaTemp" => "s",
            ]);
            return ("Agregado correctamente");
        } catch (\Throwable $th) {
            return ("Error" . $th->getMessage());
        }
    }
}
