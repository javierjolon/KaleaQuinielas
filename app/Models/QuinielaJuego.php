<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuinielaJuego extends Model
{
    use HasFactory;

    protected $table = 'quinielasJuegos';

    protected $fillable = ['quinielaId', 'usuarioId', 'juegoId', 'quinielaEquipo1', 'quinielaEquipo2', 'puntosXjuego', 'status'];

    public function juego()
    {
        return $this->belongsTo(Juegos::class, 'juegoId');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuarioId');
    }

    public function quiniela()
    {
        return $this->belongsTo(Quiniela::class, 'quinielaId');
    }
}
