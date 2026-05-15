<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Pais;
use App\Models\Quiniela;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        $paisOptions = Pais::activos()
            ->get(['code', 'name', 'dial'])
            ->mapWithKeys(fn($p) => [$p->code => "{$p->name} ({$p->dial})"])
            ->toArray();

        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->label('Nombre'),
            Forms\Components\Select::make('pais')
                ->label('País / Código de área')
                ->options($paisOptions)
                ->searchable()
                ->placeholder('Selecciona un país'),
            Forms\Components\TextInput::make('telefono')
                ->required()
                ->label('Teléfono (con código de área, ej: +50212345678)')
                ->helperText('Selecciona el país arriba para ver el código, luego escribe el número completo.'),
            Forms\Components\CheckboxList::make('roles')
                ->relationship('roles', 'name')
                ->label('Roles'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('telefono')->searchable(),
                Tables\Columns\TextColumn::make('pais')->label('País')->sortable()->searchable(),
                Tables\Columns\TagsColumn::make('quinielas.nombre')->label('Quiniela'),
            ])
            ->filters([
                SelectFilter::make('quinielas')
                    ->label('Quiniela')
                    ->relationship('quinielas', 'nombre')
                    ->multiple()
                    ->placeholder('Todas las quinielas'),
            ])
            ->defaultSort('id', 'desc')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }    
}
