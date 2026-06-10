<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiniela extends Model
{
    use HasFactory;

    protected $table = 'quinielas';

    protected $fillable = ['usuarioId', 'codigo', 'nombre', 'status'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'usuarioId');
    }

    public function juegos()
    {
        return $this->hasMany(QuinielaJuego::class, 'quinielaId');
    }

    public function usuarios()
    {
        return $this->hasMany(UsuarioQuiniela::class, 'quinielaId');
    }
}
