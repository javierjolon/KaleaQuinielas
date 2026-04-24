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
        Schema::create('torneos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        DB::table('torneos')->insert([
            ['codigo' => 'PD',  'nombre' => 'La Liga',           'activo' => true,  'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'CL',  'nombre' => 'Champions League',  'activo' => false, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'WC',  'nombre' => 'Mundial',           'activo' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('torneos');
    }
};
