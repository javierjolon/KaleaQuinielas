<?php

namespace App\Console\Commands;

use App\Http\Controllers\GamesController;
use Illuminate\Console\Command;

class Partido extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iniciar:partido';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia los partidos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GamesController $juegosController)
    {
        $this->info('Iniciando...');
        $juegosController->iniciarPartido(316, 'IN_PLAY');
        $juegosController->actualizarQuiniela();
        $this->info('Finalizado');
        return Command::SUCCESS;
    }
}
