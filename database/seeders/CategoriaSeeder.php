<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run()
    {
        $categorias = [
            ['nombre' => 'Inversiones', 'descripcion' => 'Cursos sobre cómo invertir en mercados financieros.'],
            ['nombre' => 'Ahorro', 'descripcion' => 'Técnicas y estrategias para ahorrar dinero eficientemente.'],
            ['nombre' => 'Educación Financiera', 'descripcion' => 'Bases y conceptos para manejar las finanzas personales.'],
            ['nombre' => 'Créditos y Deudas', 'descripcion' => 'Manejo responsable de créditos y deudas.'],
            ['nombre' => 'Impuestos', 'descripcion' => 'Cursos sobre legislación y pagos de impuestos.'],
            ['nombre' => 'Finanzas Corporativas', 'descripcion' => 'Gestión financiera para empresas y negocios.'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
