<?php

namespace App\Filament\Resources\QuinielaJuegoResource\Pages;

use App\Filament\Resources\QuinielaJuegoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuinielaJuego extends EditRecord
{
    protected static string $resource = QuinielaJuegoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
