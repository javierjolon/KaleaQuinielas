<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglaItem extends Model
{
    use HasFactory;

    protected $table = 'regla_items';

    protected $fillable = ['regla_grupo_id', 'descripcion', 'orden'];

    public function grupo()
    {
        return $this->belongsTo(ReglaGrupo::class, 'regla_grupo_id');
    }
}
