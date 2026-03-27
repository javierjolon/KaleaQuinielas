<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juegos extends Model
{
    use HasFactory;
    protected $fillable = ["api_id", "equipo1", "equipo2", "resultadoEquipo1", "resultadoEquipo2", "imagenEquipo1", "imagenEquipo2", "estatus", "ronda", "fechaJuego", "horaJuego"];

}
