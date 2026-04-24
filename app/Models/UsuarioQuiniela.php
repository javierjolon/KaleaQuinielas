<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioQuiniela extends Model
{
    use HasFactory;

    protected $table = 'usuariosQuinielas';

    protected $fillable = ['usuarioId', 'quinielaId', 'posicion', 'subeBaja', 'puntosAcumulados'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuarioId');
    }

    public function quiniela()
    {
        return $this->belongsTo(Quiniela::class, 'quinielaId');
    }
}
