<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {--name=} {--email=} {--password=}';
    protected $description = 'Crea un administrador de Filament';

    public function handle(): void
    {
        $name     = $this->option('name')     ?? $this->ask('Nombre');
        $email    = $this->option('email')    ?? $this->ask('Correo');
        $password = $this->option('password') ?? $this->secret('Contraseña');

        Admin::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin '{$email}' creado.");
    }
}
