<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class LogsSync extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    protected static ?string $navigationLabel = 'Logs Sync';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.logs-sync';

    public string $fechaSeleccionada = '';

    public function mount(): void
    {
        $this->fechaSeleccionada = Carbon::today()->format('Y-m-d');
    }

    public function getFechasDisponibles(): array
    {
        $files = File::glob(storage_path('logs/sync-*.log'));
        $fechas = [];

        foreach ($files as $file) {
            if (preg_match('/sync-(\d{4}-\d{2}-\d{2})\.log$/', $file, $m)) {
                $fechas[$m[1]] = Carbon::parse($m[1])->format('d/m/Y');
            }
        }

        krsort($fechas);
        return $fechas;
    }

    public function getEntradas(): array
    {
        $path = storage_path("logs/sync-{$this->fechaSeleccionada}.log");

        if (! File::exists($path)) {
            return [];
        }

        $lines   = array_filter(explode("\n", File::get($path)));
        $entries = [];

        foreach ($lines as $line) {
            if (! preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)$/', $line, $m)) {
                continue;
            }

            $mensaje = trim($m[3]);
            $json    = null;

            if (preg_match('/^(.*?)(\{.+\})\s*$/', $mensaje, $mj)) {
                $mensaje = trim($mj[1]);
                $json    = json_decode($mj[2], true);
            }

            $tipo = match (true) {
                str_contains($mensaje, 'Iniciando')           => 'inicio',
                str_contains($mensaje, 'completada')          => 'completada',
                str_contains($mensaje, 'Cambio de estatus')   => 'cambio',
                str_contains($mensaje, 'Modo espera')         => 'espera',
                str_contains($mensaje, 'Modo activo')         => 'activo',
                str_contains($mensaje, 'auto-sync')           => 'auto',
                default                                        => 'info',
            };

            $entries[] = [
                'hora'    => substr($m[1], 11),
                'nivel'   => $m[2],
                'mensaje' => $mensaje,
                'json'    => $json,
                'tipo'    => $tipo,
            ];
        }

        return array_reverse($entries);
    }
}
