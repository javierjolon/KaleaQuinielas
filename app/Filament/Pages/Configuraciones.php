<?php

namespace App\Filament\Pages;

use App\Models\Configuracion;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Configuraciones extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments';

    protected static ?string $navigationLabel = 'Configuraciones';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.configuraciones';

    public int $minutos_cierre_quiniela = 10;

    public function mount(): void
    {
        $this->minutos_cierre_quiniela = (int) Configuracion::get('minutos_cierre_quiniela', 10);
        $this->form->fill(['minutos_cierre_quiniela' => $this->minutos_cierre_quiniela]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('minutos_cierre_quiniela')
                ->label('Minutos de cierre antes del partido')
                ->helperText('Cuántos minutos antes del inicio del partido se bloquea el ingreso de quiniela.')
                ->numeric()
                ->minValue(0)
                ->maxValue(1440)
                ->required(),
        ];
    }

    public function guardar(): void
    {
        $data = $this->form->getState();

        Configuracion::where('clave', 'minutos_cierre_quiniela')
            ->update(['valor' => $data['minutos_cierre_quiniela']]);

        Notification::make()->title('Configuración guardada')->success()->send();
    }
}
