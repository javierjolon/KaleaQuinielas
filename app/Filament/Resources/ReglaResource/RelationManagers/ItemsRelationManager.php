<?php

namespace App\Filament\Resources\ReglaResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'descripcion';

    protected static ?string $label = 'Regla';

    protected static ?string $pluralLabel = 'Reglas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('descripcion')
                ->label('Descripción')
                ->required()
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('orden')
                ->label('Orden')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orden')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('descripcion')->label('Descripción')->wrap(),
            ])
            ->defaultSort('orden')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
