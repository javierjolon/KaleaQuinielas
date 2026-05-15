<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaisResource\Pages;
use App\Models\Pais;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PaisResource extends Resource
{
    protected static ?string $model = Pais::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe';

    protected static ?string $navigationLabel = 'Países';

    protected static ?string $pluralModelLabel = 'Países';

    protected static ?string $modelLabel = 'País';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('Código')
                ->required()
                ->maxLength(10)
                ->disabled(),
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(100),
            Forms\Components\TextInput::make('dial')
                ->label('Código de área')
                ->required()
                ->maxLength(10),
            Forms\Components\TextInput::make('orden')
                ->label('Orden')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('activo')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orden')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('País')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dial')
                    ->label('Código de área'),
                Tables\Columns\ToggleColumn::make('activo')
                    ->label('Activo'),
            ])
            ->defaultSort('orden')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaises::route('/'),
            'edit'  => Pages\EditPais::route('/{record}/edit'),
        ];
    }
}
