<?php

namespace App\Filament\Resources\JuegosResource\Pages;

use App\Filament\Resources\JuegosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJuegos extends EditRecord
{
    protected static string $resource = JuegosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
