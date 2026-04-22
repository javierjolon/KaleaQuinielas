<?php

namespace App\Console\Commands;

use App\Http\Controllers\GamesController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalcularPuntajes extends Command
{
    protected $signature = 'quiniela:recalcular-puntajes {--quinielaId= : ID específico de quiniela (opcional)}';
    protected $description = 'Recalcula puntajes de quinielasJuegos donde el juego ya terminó pero el status no es FINISHED/INVALID';

    public function handle(GamesController $gamesController): int
    {
        $query = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->whereIn('j.estatus', ['FINISHED', 'AWARDED'])
            ->whereNotIn('qj.status', ['FINISHED', 'INVALID'])
            ->select('qj.juegoId')
            ->distinct();

        if ($this->option('quinielaId')) {
            $query->where('qj.quinielaId', '=', intval($this->option('quinielaId')));
        }

        $juegoIds = $query->pluck('juegoId');

        if ($juegoIds->isEmpty()) {
            $this->info('No hay juegos pendientes de recalcular.');
            return Command::SUCCESS;
        }

        $this->info("Recalculando {$juegoIds->count()} juego(s)...");

        foreach ($juegoIds as $juegoId) {
            $gamesController->ApiActualizarPuntaje($juegoId, true);
            $this->line("  ✓ juegoId: {$juegoId}");
        }

        $this->info('Recálculo completado.');

        return Command::SUCCESS;
    }
}
