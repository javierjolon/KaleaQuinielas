<?php

namespace App\Console\Commands;

use App\Http\Controllers\SincronizarController;
use App\Services\apiFotballService;
use Illuminate\Console\Command;

class SyncPartidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:mundial';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza partidos del mundial';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(apiFotballService $service)
    {
        $this->info('Iniciando sincronización...');
        $respuesta = $service->sincronizarJugos();
        $this->info($respuesta['message']);
        $this->info('Fin sincronización...');
    }
}
