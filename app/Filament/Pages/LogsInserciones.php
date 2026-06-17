<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LogsInserciones extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-alt';

    protected static ?string $navigationLabel = 'Logs Inserciones';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.logs-inserciones';

    public string $fechaSeleccionada = '';

    public function mount(): void
    {
        $fechas = $this->getFechasDisponibles();
        $this->fechaSeleccionada = $fechas ? array_key_first($fechas) : '';
    }

    public function getFechasDisponibles(): array
    {
        $files  = File::glob(storage_path('logs/inserciones-*.log'));
        $fechas = [];

        foreach ($files as $file) {
            if (preg_match('/inserciones-(\d{4}-\d{2}-\d{2})\.log$/', $file, $m)) {
                $fechas[$m[1]] = Carbon::parse($m[1])->format('d/m/Y');
            }
        }

        krsort($fechas);
        return $fechas;
    }

    public function getEntradas(): array
    {
        $path = storage_path("logs/inserciones-{$this->fechaSeleccionada}.log");

        if (! File::exists($path)) {
            return [];
        }

        $lines   = array_filter(explode("\n", File::get($path)));
        $entries = [];
        $userIds = [];

        $parsed = [];
        foreach ($lines as $line) {
            if (! preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.\w+: \w+ (\{.+\})\s*$/', $line, $m)) {
                continue;
            }

            $json = json_decode($m[2], true);
            if (! $json) {
                continue;
            }

            $parsed[]  = ['hora' => substr($m[1], 11), 'datos' => $json];
            $userIds[] = $json['usuarioId'];
        }

        $quinielaIds = array_unique(array_column(array_column($parsed, 'datos'), 'quinielaId'));
        $juegoIds    = array_unique(array_column(array_column($parsed, 'datos'), 'juegoId'));

        $usuarios = DB::table('users')
            ->whereIn('id', array_unique($userIds))
            ->pluck('name', 'id');

        $quinielas = DB::table('quinielas')
            ->whereIn('id', $quinielaIds)
            ->pluck('nombre', 'id');

        $juegos = DB::table('juegos')
            ->whereIn('id', $juegoIds)
            ->select('id', 'equipo1', 'equipo2')
            ->get()
            ->keyBy('id');

        foreach ($parsed as $p) {
            $d      = $p['datos'];
            $juego  = $juegos[$d['juegoId']] ?? null;
            $entries[] = [
                'hora'     => $p['hora'],
                'accion'   => $d['accion'] ?? 'INSERT',
                'usuario'  => $usuarios[$d['usuarioId']] ?? "ID {$d['usuarioId']}",
                'quiniela' => $quinielas[$d['quinielaId']] ?? "#{$d['quinielaId']}",
                'juego'    => $juego ? "{$juego->equipo1} vs {$juego->equipo2}" : "#{$d['juegoId']}",
                'nuevo'    => ($d['quinielaEquipo1'] ?? '-') . ' - ' . ($d['quinielaEquipo2'] ?? '-'),
                'anterior' => is_null($d['quinielaEquipo1Anterior'] ?? null) && is_null($d['quinielaEquipo2Anterior'] ?? null)
                                ? null
                                : ($d['quinielaEquipo1Anterior'] ?? '-') . ' - ' . ($d['quinielaEquipo2Anterior'] ?? '-'),
                'ip'       => $d['ip'] ?? '',
            ];
        }

        return array_reverse($entries);
    }
}
