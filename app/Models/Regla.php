<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regla extends Model
{
    use HasFactory;

    protected $table = 'reglas';

    protected $fillable = ['titulo', 'descripcion', 'orden', 'activa'];

    protected $casts = ['activa' => 'boolean'];
}
