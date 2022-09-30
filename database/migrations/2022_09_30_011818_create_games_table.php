<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("team1");
            $table->foreign('team1')->references('id')->on('teams');
            $table->integer("score1")->nullable();
            $table->unsignedBigInteger("team2");
            $table->foreign('team2')->references('id')->on('teams');
            $table->integer("score2")->nullable();
            $table->date("dateGame");
            $table->time("timeGame");
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
        Schema::dropIfExists('games');
    }
}
