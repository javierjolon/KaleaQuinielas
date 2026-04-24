<?php

namespace App\Filament\Pages;

use App\Http\Controllers\GamesController;
use App\Services\apiFotballService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Acciones extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-lightning-bolt';

    protected static ?string $navigationLabel = 'Acciones';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.acciones';

    public function syncApi(): void
    {
        app(apiFotballService::class)->sincronizarJugos('PD');
        Notification::make()->title('Sincronización completada')->success()->send();
    }

    public function recalcularInvalidos(): void
    {
        $juegoIds = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->where('qj.status', 'INVALID')
            ->whereNotNull('qj.quinielaEquipo1')
            ->whereNotNull('qj.quinielaEquipo2')
            ->whereIn('j.estatus', ['FINISHED', 'AWARDED'])
            ->select('qj.juegoId')
            ->distinct()
            ->pluck('juegoId');

        $ctrl = app(GamesController::class);
        foreach ($juegoIds as $id) {
            $ctrl->ApiActualizarPuntaje($id, true);
        }

        Notification::make()
            ->title("Recalculados: {$juegoIds->count()} juego(s)")
            ->success()
            ->send();
    }

    public function recalcularPuntajes(): void
    {
        $juegoIds = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->whereIn('j.estatus', ['FINISHED', 'AWARDED'])
            ->whereNotIn('qj.status', ['FINISHED', 'INVALID'])
            ->select('qj.juegoId')
            ->distinct()
            ->pluck('juegoId');

        $ctrl = app(GamesController::class);
        foreach ($juegoIds as $id) {
            $ctrl->ApiActualizarPuntaje($id, true);
        }

        Notification::make()
            ->title("Recalculados: {$juegoIds->count()} juego(s)")
            ->success()
            ->send();
    }
}
