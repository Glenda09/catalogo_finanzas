<?php

namespace App\Services;

use App\Models\Usuarios;
use Laravel\Passport\Passport;

class LoginTestService
{
    public function login(): Usuarios
    {
        $usuario = Usuarios::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

          auth()->login($usuario);

        return $usuario;
    }
}
