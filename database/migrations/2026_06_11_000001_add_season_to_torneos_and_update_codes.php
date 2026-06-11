<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->unsignedSmallInteger('season')->default(2024)->after('activo');
        });

        // Actualizar códigos de football-data.org a IDs numéricos de api-sports.io
        DB::table('torneos')->where('codigo', 'PD')->update(['codigo' => '140', 'season' => 2024]);
        DB::table('torneos')->where('codigo', 'CL')->update(['codigo' => '2',   'season' => 2024]);
        DB::table('torneos')->where('codigo', 'WC')->update(['codigo' => '1',   'season' => 2026]);
    }

    public function down(): void
    {
        DB::table('torneos')->where('codigo', '140')->update(['codigo' => 'PD']);
        DB::table('torneos')->where('codigo', '2')->update(['codigo' => 'CL']);
        DB::table('torneos')->where('codigo', '1')->update(['codigo' => 'WC']);

        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn('season');
        });
    }
};
