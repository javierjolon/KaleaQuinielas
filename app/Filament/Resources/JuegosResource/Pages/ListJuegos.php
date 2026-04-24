<?php

namespace App\Filament\Resources\JuegosResource\Pages;

use App\Filament\Resources\JuegosResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJuegos extends ListRecords
{
    protected static string $resource = JuegosResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
