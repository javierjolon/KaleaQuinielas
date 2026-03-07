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
        Schema::table('game', function(Blueprint $table){
            $table->string('typeGame')->change();
            $table->string('team1')->nullable()->change();
            $table->string('team2')->nullable()->change();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game', function(Blueprint $table){
            $table->integer('typeGame')->change();
            $table->unsignedBigInteger('team1')->change();
            $table->unsignedBigInteger('team2')->change();
        });
    }
};
