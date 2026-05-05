<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('reglas');

        Schema::create('regla_grupos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('regla_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regla_grupo_id')->constrained('regla_grupos')->cascadeOnDelete();
            $table->text('descripcion');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('regla_items');
        Schema::dropIfExists('regla_grupos');

        Schema::create('reglas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }
};
