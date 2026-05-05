<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReglaResource\Pages;
use App\Filament\Resources\ReglaResource\RelationManagers\ItemsRelationManager;
use App\Models\ReglaGrupo;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ReglaResource extends Resource
{
    protected static ?string $model = ReglaGrupo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Reglas';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('titulo')
                ->label('Título')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('orden')
                ->label('Orden')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('activo')
                ->label('Visible')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orden')->sortable()->label('#'),
                Tables\Columns\TextColumn::make('titulo')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Reglas')
                    ->counts('items'),
                Tables\Columns\IconColumn::make('activo')->boolean()->label('Visible'),
            ])
            ->defaultSort('orden')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReglas::route('/'),
            'create' => Pages\CreateRegla::route('/create'),
            'edit'   => Pages\EditRegla::route('/{record}/edit'),
        ];
    }
}
