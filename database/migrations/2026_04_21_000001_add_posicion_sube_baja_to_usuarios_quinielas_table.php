<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('usuariosQuinielas', function (Blueprint $table) {
            $table->integer('posicion')->default(0);
            $table->string('subeBaja')->default('i');
        });
    }

    public function down()
    {
        Schema::table('usuariosQuinielas', function (Blueprint $table) {
            $table->dropColumn(['posicion', 'subeBaja']);
        });
    }
};
