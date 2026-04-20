<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarcarQuinielasInvalidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quiniela:marcar-invalidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca en INVALID las quinielas sin captura fuera de tiempo';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $actualizados = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->whereNull('qj.quinielaEquipo1')
            ->whereNull('qj.quinielaEquipo2')
            ->whereNotIn('qj.status', ['INVALID', 'FINISHED'])
            ->whereRaw("NOW() >= DATE_SUB(TIMESTAMP(j.fechaJuego, j.horaJuego), INTERVAL 10 MINUTE)")
            ->update([
                'qj.status' => 'INVALID',
                'qj.updated_at' => now(),
            ]);

        $this->info("Quinielas actualizadas a INVALID: {$actualizados}");

        return Command::SUCCESS;
    }
}
