<?php

namespace App\Filament\Resources\QuinielaJuegoResource\Pages;

use App\Filament\Resources\QuinielaJuegoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuinielaJuegos extends ListRecords
{
    protected static string $resource = QuinielaJuegoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
