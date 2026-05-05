<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

class Admin extends \Illuminate\Database\Eloquent\Model implements
    AuthenticatableContract,
    \Illuminate\Contracts\Auth\Access\Authorizable,
    CanResetPasswordContract,
    FilamentUser
{
    use Authenticatable, Authorizable, CanResetPassword, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [];

    public function canAccessFilament(): bool
    {
        return true;
    }
}
