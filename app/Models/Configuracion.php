<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'valor', 'descripcion'];

    public static function get(string $clave, $default = null)
    {
        return static::where('clave', $clave)->value('valor') ?? $default;
    }
}
