<?php

namespace App\Filament\Resources\ReglaResource\Pages;

use App\Filament\Resources\ReglaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReglas extends ListRecords
{
    protected static string $resource = ReglaResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
