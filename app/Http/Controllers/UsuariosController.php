<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UsuariosController extends Controller
{
    public function enrolarUsuarioAQuiniela($usuarioId, $quinielaId){ 
        //crea una nueva entrada en la tabla usuariosQuinielas con el usuarioId y la quinielaId
        DB::table("usuariosQuinielas")
        ->insert([
            "usuarioId" => $usuarioId,
            "quinielaId" => $quinielaId,
        ]);


        
        return ("Usuario agregado a la quiniela correctamente");
    }

    // Crea un nuevo usuario en la base de datos
    public function crear($nombre, $telefono){
        try {
            DB::table("users")
            ->insert([
                "name" => $nombre,
                "telefono" => $telefono,
                "password" => Hash::make(Str::random(32)),
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
