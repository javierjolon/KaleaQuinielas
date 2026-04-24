<?php

namespace App\Console\Commands;

use App\Services\apiFotballService;
use Illuminate\Console\Command;

class SincronizarApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiniela:sincronizarApi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza todos los partidos desde el API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(apiFotballService $service)
    {
        $this->info('Iniciando sincronización...');
        $service->sincronizarTodos();
        $this->info('Fin sincronización...');
    }
}
