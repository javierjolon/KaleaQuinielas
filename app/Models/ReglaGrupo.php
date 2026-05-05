<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglaGrupo extends Model
{
    use HasFactory;

    protected $table = 'regla_grupos';

    protected $fillable = ['titulo', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function items()
    {
        return $this->hasMany(ReglaItem::class, 'regla_grupo_id')->orderBy('orden');
    }
}
