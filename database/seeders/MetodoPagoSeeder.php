<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $metodos = [
            ['nombre' => 'Tarjeta de Crédito', 'activo' => true],
            ['nombre' => 'Tarjeta de Débito', 'activo' => true],
            ['nombre' => 'PayPal', 'activo' => true],
            ['nombre' => 'Transferencia Bancaria', 'activo' => true],
            ['nombre' => 'Efectivo', 'activo' => true],
            ['nombre' => 'Criptomonedas', 'activo' => true],
            ['nombre' => 'Pago móvil', 'activo' => true],
        ];

        DB::table('metodo_pagos')->insert($metodos);
    }
}
