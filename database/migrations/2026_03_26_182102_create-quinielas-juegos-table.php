<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quinielasJuegos', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string("quinielaId");
            $table->integer("usuarioId");
            $table->integer("juegoId");
            $table->integer("quinielaEquipo1")->nullable();
            $table->integer("quinielaEquipo2")->nullable();
            $table->integer("puntosXjuego")->default(0);
            $table->string("status");
            $table->string("fechaJuego");
            $table->string("horaJuego");
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
        Schema::dropIfExists('quinielasJuegos');
    }
};
