<?php

namespace App\Filament\Resources\TorneoResource\Pages;

use App\Filament\Resources\TorneoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTorneo extends EditRecord
{
    protected static string $resource = TorneoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
