<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Torneo extends Model
{
    use HasFactory;

    protected $table = 'torneos';

    protected $fillable = ['codigo', 'nombre', 'activo', 'season'];

    protected $casts = ['activo' => 'boolean'];

    public static function activos()
    {
        return static::where('activo', true)->get(['codigo', 'season', 'nombre']);
    }
}
