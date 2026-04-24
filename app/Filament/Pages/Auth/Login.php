<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Http\Livewire\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email')
                ->label('Teléfono')
                ->tel()
                ->required()
                ->autocomplete(),
            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->required(),
        ];
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'telefono' => $data['email'],
            'password' => $data['password'],
        ];
    }
}
