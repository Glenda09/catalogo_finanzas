<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Usuarios;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = 'admin@finanzas.com';

        $admin = Usuarios::firstOrCreate(
            ['email' => $adminEmail],
            [
                'nombre' => 'Super Administrador',
                'apellido' => 'Finanzas',
                'email' => 'finanzas@gmail.com',
                'telefono' => '1234567890',
                'password' => bcrypt('Admin123'),
            ]
        );

        $rolAdmin = Role::where('nombre', 'admin')->first();

        if ($rolAdmin && !$admin->roles->contains($rolAdmin->id)) {
            $admin->roles()->attach($rolAdmin->id);
        }
    }
}
