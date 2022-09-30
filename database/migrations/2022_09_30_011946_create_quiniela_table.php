<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuinielaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiniela', function (Blueprint $table) {
            $table->id();
            $table->integer("userId");
            $table->integer("gameId");
            $table->integer("scoreTeam1");
            $table->integer("scoreTeam2");
            $table->integer("pointsXGame");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quiniela');
    }
}
