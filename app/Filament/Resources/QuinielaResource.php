<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuinielaResource\Pages;
use App\Models\Quiniela;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class QuinielaResource extends Resource
{
    protected static ?string $model = Quiniela::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $navigationLabel = 'Quinielas';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('codigo')->label('Código')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->sortable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Dueño')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner.telefono')
                    ->label('Teléfono dueño'),
                Tables\Columns\TextColumn::make('usuarios_count')
                    ->label('Participantes')
                    ->counts('usuarios')
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('nombre')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuinielas::route('/'),
        ];
    }
}
