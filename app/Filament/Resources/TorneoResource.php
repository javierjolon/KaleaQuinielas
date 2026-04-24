<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TorneoResource\Pages;
use App\Models\Torneo;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class TorneoResource extends Resource
{
    protected static ?string $model = Torneo::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe';

    protected static ?string $navigationLabel = 'Torneos';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('codigo')
                ->label('Código API')
                ->helperText('Ej: PD, CL, WC, BL1, SA — código de football-data.org')
                ->required()
                ->maxLength(10)
                ->uppercase(),
            Forms\Components\TextInput::make('nombre')
                ->label('Nombre')
                ->required(),
            Forms\Components\Toggle::make('activo')
                ->label('Sincronizar activamente')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->label('Código')->sortable(),
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\IconColumn::make('activo')->boolean()->label('Activo'),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizado')->dateTime()->sortable(),
            ])
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
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTorneos::route('/'),
            'create' => Pages\CreateTorneo::route('/create'),
            'edit' => Pages\EditTorneo::route('/{record}/edit'),
        ];
    }    
}
