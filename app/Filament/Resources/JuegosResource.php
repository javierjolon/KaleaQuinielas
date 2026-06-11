<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JuegosResource\Pages;
use App\Models\Juegos;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class JuegosResource extends Resource
{
    protected static ?string $model = Juegos::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Partidos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('equipo1')->required(),
            Forms\Components\TextInput::make('equipo2')->required(),
            Forms\Components\TextInput::make('resultadoEquipo1')->numeric()->nullable(),
            Forms\Components\TextInput::make('resultadoEquipo2')->numeric()->nullable(),
            Forms\Components\Select::make('estatus')
                ->options([
                    'TIMED'     => 'Programado',
                    'SCHEDULED' => 'Agendado',
                    'IN_PLAY'   => 'En juego',
                    'PAUSED'    => 'Medio tiempo',
                    'FINISHED'  => 'Finalizado',
                    'AWARDED'   => 'Adjudicado',
                    'CANCELLED' => 'Cancelado',
                    'POSTPONED' => 'Pospuesto',
                    'SUSPENDED' => 'Suspendido',
                ])
                ->required(),
            Forms\Components\TextInput::make('ronda')->label('Ronda'),
            Forms\Components\TextInput::make('competicion'),
            Forms\Components\DatePicker::make('fechaJuego'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('equipo1')->searchable(),
                Tables\Columns\TextColumn::make('equipo2')->searchable(),
                Tables\Columns\TextColumn::make('resultadoEquipo1')->label('G1'),
                Tables\Columns\TextColumn::make('resultadoEquipo2')->label('G2'),
                Tables\Columns\BadgeColumn::make('estatus')
                    ->colors([
                        'success' => 'FINISHED',
                        'warning' => fn ($state) => in_array($state, ['IN_PLAY', 'PAUSED']),
                        'secondary' => fn ($state) => in_array($state, ['TIMED', 'SCHEDULED']),
                        'danger' => fn ($state) => in_array($state, ['CANCELLED', 'POSTPONED', 'SUSPENDED']),
                    ]),
                Tables\Columns\TextColumn::make('ronda')->label('Ronda')->sortable(),
                Tables\Columns\TextColumn::make('competicion'),
                Tables\Columns\TextColumn::make('fechaJuego')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estatus')
                    ->options([
                        'IN_PLAY'  => 'En juego',
                        'PAUSED'   => 'Medio tiempo',
                        'FINISHED' => 'Finalizado',
                        'TIMED'    => 'Programado',
                    ]),
                Tables\Filters\SelectFilter::make('competicion')
                    ->options(['140' => 'La Liga', '2' => 'Champions', '1' => 'Mundial']),
            ])
            ->defaultSort('fechaJuego', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListJuegos::route('/'),
            'create' => Pages\CreateJuegos::route('/create'),
            'edit' => Pages\EditJuegos::route('/{record}/edit'),
        ];
    }    
}
