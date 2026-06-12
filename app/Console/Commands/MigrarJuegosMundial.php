<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarJuegosMundial extends Command
{
    protected $signature   = 'quiniela:migrar-juegos-mundial {--dry-run : Solo mostrar cambios sin aplicarlos}';
    protected $description = 'Migra quinielasJuegos de registros WC (football-data.org) a registros competicion=1 (api-sports.io)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('=== DRY RUN — sin cambios en BD ===');
        }

        $wcJuegos  = DB::table('juegos')->where('competicion', 'WC')->where('season', 2026)->get();
        $newJuegos = DB::table('juegos')->where('competicion', '1')->where('season', 2026)->get()
            ->keyBy(fn($j) => $j->fechaJuego . '|' . $j->horaJuego . '|' . strtolower($j->equipo1 ?? ''));

        $this->info("WC legacy: {$wcJuegos->count()} | Nuevos api-sports.io: {$newJuegos->count()}");

        $migrados   = 0;
        $sinMatch   = [];

        foreach ($wcJuegos as $wc) {
            $newJuego = $this->encontrarMatch($wc, $newJuegos);

            if (! $newJuego) {
                $sinMatch[] = "id={$wc->id} | {$wc->equipo1} vs {$wc->equipo2} | {$wc->fechaJuego} {$wc->horaJuego}";
                continue;
            }

            $refsEnWc  = DB::table('quinielasJuegos')->where('juegoId', $wc->id)->count();
            $refsEnNew = DB::table('quinielasJuegos')->where('juegoId', $newJuego->id)->count();

            $this->line(
                "  WC id={$wc->id} ({$wc->equipo1}) → new id={$newJuego->id} | " .
                "refs WC={$refsEnWc} new={$refsEnNew} | estatus WC={$wc->estatus} new={$newJuego->estatus}"
            );

            if (! $dryRun) {
                DB::transaction(function () use ($wc, $newJuego, $refsEnWc, $refsEnNew) {
                    if ($refsEnWc > 0) {
                        // Mover referencias al registro nuevo
                        DB::table('quinielasJuegos')
                            ->where('juegoId', $wc->id)
                            ->update(['juegoId' => $newJuego->id]);
                    }

                    if ($refsEnNew === 0 && $refsEnWc === 0) {
                        // Nadie apunta a ninguno — eliminar WC y dejar nuevo
                        DB::table('juegos')->where('id', $wc->id)->delete();
                        return;
                    }

                    // Eliminar registro WC (ya no tiene referencias)
                    DB::table('juegos')->where('id', $wc->id)->delete();
                });
            }

            $migrados++;
        }

        $this->info("Migrados: {$migrados}");

        if (count($sinMatch) > 0) {
            $this->warn('Sin match (probablemente rondas eliminatorias sin equipos definidos aún):');
            foreach ($sinMatch as $s) {
                $this->line("  {$s}");
            }
        }

        return Command::SUCCESS;
    }

    private function encontrarMatch($wc, $newJuegos): ?object
    {
        if (! $wc->equipo1) {
            // Partido eliminatorio sin equipos — buscar solo por fecha y hora
            return $newJuegos
                ->filter(fn($j) => $j->fechaJuego === $wc->fechaJuego && $j->horaJuego === $wc->horaJuego && ! $j->equipo1)
                ->first();
        }

        $primeraPalabraWc = strtolower(explode(' ', trim($wc->equipo1))[0]);
        $fecha = $wc->fechaJuego;
        $hora  = $wc->horaJuego;

        // Intento 1: fecha + hora + primera palabra equipo1
        $candidatos = $newJuegos->filter(
            fn($j) => $j->fechaJuego === $fecha && $j->horaJuego === $hora
        );

        if ($candidatos->count() === 1) {
            return $candidatos->first();
        }

        // Varios partidos a la misma hora — desambiguar por equipo1
        $match = $candidatos->first(
            fn($j) => $j->equipo1 && str_contains(strtolower($j->equipo1), $primeraPalabraWc)
        );

        if ($match) {
            return $match;
        }

        // Intento inverso: nombre WC contiene primera palabra del nuevo
        return $candidatos->first(function ($j) use ($primeraPalabraWc, $wc) {
            $primeraPalabraNew = strtolower(explode(' ', trim($j->equipo1 ?? ''))[0]);
            return str_contains(strtolower($wc->equipo1), $primeraPalabraNew)
                || str_contains($primeraPalabraNew, $primeraPalabraWc);
        });
    }
}
