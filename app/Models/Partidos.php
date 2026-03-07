<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partidos extends Model
{
    use HasFactory;
    protected $table = "game";
    protected $fillable = ["api_id", "team1", "score1", "team2", "score2", "typeGame", "dateGame", "status", "timeGame", "imagenTeam1", "imagenTeam2"];
}
