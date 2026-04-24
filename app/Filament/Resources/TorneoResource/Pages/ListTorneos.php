<?php

namespace App\Filament\Resources\TorneoResource\Pages;

use App\Filament\Resources\TorneoResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTorneos extends ListRecords
{
    protected static string $resource = TorneoResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
