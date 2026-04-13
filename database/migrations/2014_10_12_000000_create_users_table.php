<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->unique()->autoIncrement();
            $table->string('name');
            $table->string('telefono')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('posicionActual')->default(0);
            $table->integer('posicionActualTemp')->default(0);
            $table->integer('puntosAcumulados')->default(0);
            $table->integer('puntosAcumuladosTemp')->default(0);
            $table->string('subeBaja')->default('s');
            $table->string('subeBajaTemp')->default('s');
            $table->string('foto')->nullable();
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
        Schema::dropIfExists('users');
    }
}
