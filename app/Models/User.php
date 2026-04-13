<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends \Illuminate\Database\Eloquent\Model implements
    AuthenticatableContract,
    \Illuminate\Contracts\Auth\Access\Authorizable,
    CanResetPasswordContract,
    MustVerifyEmail
{
    use Authenticatable, Authorizable, CanResetPassword, HasFactory, HasRoles, MustVerifyEmailTrait, Notifiable;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'telefono', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Valor usado en enlaces de verificación (históricamente “email” en Laravel).
     */
    public function getEmailForVerification(): string
    {
        return $this->telefono;
    }

    /**
     * Columna usada por recuperación de contraseña (nombre histórico en Laravel).
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->telefono;
    }

    /**
     * Destino del correo con el enlace de restablecimiento (sin SMS configurado).
     */
    public function routeNotificationForMail($notification = null)
    {
        if ($notification instanceof \Illuminate\Auth\Notifications\ResetPassword) {
            $admin = config('app.admin_email_for_password_resets');
            if ($admin) {
                return $admin;
            }
            if (app()->environment('testing')) {
                return 'test@example.com';
            }

            return config('mail.from.address');
        }

        if ($notification instanceof \Illuminate\Auth\Notifications\VerifyEmail) {
            $admin = config('app.admin_email_for_password_resets');
            if ($admin) {
                return $admin;
            }
            if (app()->environment('testing')) {
                return 'test@example.com';
            }

            return config('mail.from.address');
        }

        return null;
    }
}
