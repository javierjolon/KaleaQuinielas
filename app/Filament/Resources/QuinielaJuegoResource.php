<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuinielaJuegoResource\Pages;
use App\Http\Controllers\GamesController;
use App\Models\Juegos;
use App\Models\QuinielaJuego;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class QuinielaJuegoResource extends Resource
{
    protected static ?string $model = QuinielaJuego::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-list';

    protected static ?string $navigationLabel = 'Predicciones';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('quinielaEquipo1')->numeric()->nullable()->label('Predicción L'),
            Forms\Components\TextInput::make('quinielaEquipo2')->numeric()->nullable()->label('Predicción V'),
            Forms\Components\TextInput::make('puntosXjuego')->numeric()->label('Puntos'),
            Forms\Components\Select::make('status')
                ->options([
                    'PENDING'  => 'Pendiente',
                    'TIMED'    => 'Programado',
                    'IN_PLAY'  => 'En juego',
                    'FINISHED' => 'Finalizado',
                    'INVALID'  => 'No válido',
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('usuario.name')->label('Usuario')->searchable(),
                Tables\Columns\TextColumn::make('juego.equipo1')->label('Local'),
                Tables\Columns\TextColumn::make('juego.equipo2')->label('Visitante'),
                Tables\Columns\TextColumn::make('quinielaEquipo1')->label('Pred L'),
                Tables\Columns\TextColumn::make('quinielaEquipo2')->label('Pred V'),
                Tables\Columns\TextColumn::make('puntosXjuego')->label('Pts')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'FINISHED',
                        'warning' => 'IN_PLAY',
                        'danger'  => 'INVALID',
                        'secondary' => fn ($state) => in_array($state, ['PENDING', 'TIMED']),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('juegoId')
                    ->label('Juego')
                    ->options(
                        Juegos::orderBy('fechaJuego')->get()
                            ->mapWithKeys(fn ($j) => [$j->id => "{$j->equipo1} vs {$j->equipo2}"])
                    )
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'INVALID'  => 'No válido',
                        'FINISHED' => 'Finalizado',
                        'IN_PLAY'  => 'En juego',
                        'PENDING'  => 'Pendiente',
                    ]),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('recalcular')
                    ->label('Recalcular')
                    ->icon('heroicon-o-refresh')
                    ->action(function (QuinielaJuego $record) {
                        app(GamesController::class)->ApiActualizarPuntaje($record->juegoId, true);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('recalcular_seleccionados')
                    ->label('Recalcular seleccionados')
                    ->icon('heroicon-o-refresh')
                    ->action(function ($records) {
                        $ctrl = app(GamesController::class);
                        $records->each(fn ($r) => $ctrl->ApiActualizarPuntaje($r->juegoId, true));
                    })
                    ->requiresConfirmation(),
            ]);
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
            'index' => Pages\ListQuinielaJuegos::route('/'),
            'create' => Pages\CreateQuinielaJuego::route('/create'),
            'edit' => Pages\EditQuinielaJuego::route('/{record}/edit'),
        ];
    }    
}
