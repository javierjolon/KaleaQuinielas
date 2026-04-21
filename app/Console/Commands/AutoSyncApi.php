<?php

namespace App\Console\Commands;

use App\Models\Juegos;
use App\Services\apiFotballService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AutoSyncApi extends Command
{
    protected $signature = 'quiniela:auto-sync {--reset : Reinicia el estado del scheduler}';
    protected $description = 'Sincronización inteligente: activa durante juegos, espera hasta el próximo partido';

    private const ACTIVE_INTERVAL_MINUTES = 5;
    private const PRE_GAME_SECONDS        = 600; // 10 min antes activar modo activo
    private const CACHE_TTL               = 86400;

    public function handle(apiFotballService $service): void
    {
        if ($this->option('reset')) {
            $this->resetState();
            $this->info('Estado del scheduler reiniciado.');
            return;
        }

        $mode = Cache::get('auto_sync_mode', 'idle');

        match ($mode) {
            'active'  => $this->handleActive($service),
            'waiting' => $this->handleWaiting($service),
            default   => $this->handleIdle($service),
        };
    }

    private function handleActive(apiFotballService $service): void
    {
        if (!Juegos::whereIn('estatus', ['IN_PLAY', 'PAUSED'])->exists()) {
            $this->info('Sin juegos en curso — buscando próximo partido...');
            $this->transitionToWaiting();
            return;
        }

        $lastSync      = Cache::get('auto_sync_last_at', 0);
        $minutesPassed = (now()->timestamp - $lastSync) / 60;

        if ($minutesPassed >= self::ACTIVE_INTERVAL_MINUTES) {
            $this->info('Juego en curso — sincronizando...');
            $service->sincronizarJugos('PD');
            Cache::put('auto_sync_last_at', now()->timestamp, self::CACHE_TTL);
        }
    }

    private function handleWaiting(apiFotballService $service): void
    {
        // Juego arrancó antes de lo esperado
        if (Juegos::whereIn('estatus', ['IN_PLAY', 'PAUSED'])->exists()) {
            $this->info('Juego detectado — cambiando a modo activo');
            $this->enterActiveMode();
            return;
        }

        $nextGameAt   = Cache::get('auto_sync_next_game_at');
        $waitStartAt  = Cache::get('auto_sync_wait_start_at');
        $midSyncsDone = Cache::get('auto_sync_mid_syncs_done', 0);

        if (!$nextGameAt || !$waitStartAt) {
            $this->transitionToWaiting();
            return;
        }

        $now       = now()->timestamp;
        $totalWait = $nextGameAt - $waitStartAt;
        $mid1      = (int) ($waitStartAt + $totalWait * (1 / 3));
        $mid2      = (int) ($waitStartAt + $totalWait * (2 / 3));

        if ($midSyncsDone === 0 && $now >= $mid1) {
            $this->info('Sync intermedio 1/2 — verificando cambios de horario...');
            $service->sincronizarJugos('PD');
            Cache::put('auto_sync_mid_syncs_done', 1, self::CACHE_TTL);
            $this->refreshNextGameTime(); // recalcular por si cambió el horario
        } elseif ($midSyncsDone === 1 && $now >= $mid2) {
            $this->info('Sync intermedio 2/2 — verificando cambios de horario...');
            $service->sincronizarJugos('PD');
            Cache::put('auto_sync_mid_syncs_done', 2, self::CACHE_TTL);
            $this->refreshNextGameTime();
        }

        // Recargar por si refreshNextGameTime actualizó el valor
        $nextGameAt = Cache::get('auto_sync_next_game_at', $nextGameAt);

        if ($now >= $nextGameAt - self::PRE_GAME_SECONDS) {
            $this->info('Partido próximo — cambiando a modo activo');
            $this->enterActiveMode();
        }
    }

    private function handleIdle(apiFotballService $service): void
    {
        if (Juegos::whereIn('estatus', ['IN_PLAY', 'PAUSED'])->exists()) {
            $this->info('Juego en curso detectado desde idle — modo activo');
            $this->enterActiveMode();
            return;
        }

        $this->transitionToWaiting();
    }

    private function transitionToWaiting(): void
    {
        $nextGame = Juegos::whereIn('estatus', ['TIMED', 'SCHEDULED'])
            ->where('fechaJuego', '>', now())
            ->orderBy('fechaJuego')
            ->first();

        if (!$nextGame) {
            $this->info('Sin próximos partidos programados — modo idle');
            Cache::put('auto_sync_mode', 'idle', self::CACHE_TTL);
            return;
        }

        $nextGameAt = Carbon::parse($nextGame->fechaJuego)->timestamp;
        $now        = now()->timestamp;

        if ($nextGameAt - $now <= self::PRE_GAME_SECONDS) {
            $this->info('Partido en menos de 10 min — modo activo directo');
            $this->enterActiveMode();
            return;
        }

        Cache::put('auto_sync_mode', 'waiting', self::CACHE_TTL);
        Cache::put('auto_sync_next_game_at', $nextGameAt, self::CACHE_TTL);
        Cache::put('auto_sync_wait_start_at', $now, self::CACHE_TTL);
        Cache::put('auto_sync_mid_syncs_done', 0, self::CACHE_TTL);

        $this->info('Modo espera hasta: ' . $nextGame->fechaJuego);
    }

    private function refreshNextGameTime(): void
    {
        $nextGame = Juegos::whereIn('estatus', ['TIMED', 'SCHEDULED'])
            ->where('fechaJuego', '>', now())
            ->orderBy('fechaJuego')
            ->first();

        if ($nextGame) {
            $newTimestamp = Carbon::parse($nextGame->fechaJuego)->timestamp;
            $oldTimestamp = Cache::get('auto_sync_next_game_at');

            if ($newTimestamp !== $oldTimestamp) {
                $this->info('Horario del próximo partido actualizado: ' . $nextGame->fechaJuego);
                Cache::put('auto_sync_next_game_at', $newTimestamp, self::CACHE_TTL);
            }
        }
    }

    private function enterActiveMode(): void
    {
        Cache::put('auto_sync_mode', 'active', self::CACHE_TTL);
        Cache::put('auto_sync_last_at', 0, self::CACHE_TTL);
    }

    private function resetState(): void
    {
        Cache::forget('auto_sync_mode');
        Cache::forget('auto_sync_last_at');
        Cache::forget('auto_sync_next_game_at');
        Cache::forget('auto_sync_wait_start_at');
        Cache::forget('auto_sync_mid_syncs_done');
    }
}
