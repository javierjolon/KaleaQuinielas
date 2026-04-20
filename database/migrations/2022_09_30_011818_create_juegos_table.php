<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJuegosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('juegos', function (Blueprint $table) {
            $table->id();
            $table->integer("api_id")->unique();
            $table->string("equipo1")->nullable();
            $table->string("equipo2")->nullable();
            $table->integer("resultadoEquipo1")->nullable();
            $table->integer("resultadoEquipo2")->nullable();
            $table->string("imagenEquipo1")->nullable();
            $table->string("imagenEquipo2")->nullable();
            $table->string("estatus");
            $table->string('ronda');
            $table->string('competicion');
            $table->date("fechaJuego");
            $table->time("horaJuego");
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
        Schema::dropIfExists('juegos');
    }
}
