<?php

namespace App\Services;

use App\Models\Usuarios;
use Laravel\Passport\Passport;

class LoginTestService
{
    public function login(array $claims = [])
    {
        $usuario = Usuarios::create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Passport::actingAs($usuario, $claims);

        return $usuario;
    }
}
