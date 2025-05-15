<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;

class CursoController extends Controller
{
    /**
     * Muestra una lista de los recursos Curso.
     *
     * Este método recupera y devuelve una colección de cursos disponibles en el sistema.
     * Generalmente se utiliza para mostrar todos los cursos en una vista de listado.
     *
     * @return \Illuminate\Http\Response Respuesta HTTP con la lista de cursos.
     */
    public function index(Request $request)
    {
        $query = Curso::query();

        if ($request->filled('categoria')) {
            $query->whereHas('categoria', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->categoria . '%');
            });
        }

        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        $cursos = $query->paginate($request->get('per_page', 10));

        return response()->json($cursos);
    }

    /**
     * Realiza una operación específica basada en los parámetros proporcionados.
     *
     * Este método toma los valores de entrada y ejecuta la lógica principal del proceso,
     * devolviendo el resultado correspondiente. Asegúrate de validar los parámetros antes de llamar a este método.
     *
     * @param int $param1 El primer parámetro necesario para la operación.
     * @param string $param2 Una cadena que influye en el comportamiento del método.
     * @return bool Devuelve true si la operación fue exitosa, false en caso contrario.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'duracion_horas' => 'required|integer|min:1',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        $curso = Curso::create($validated);

        return response()->json($curso, 201);
    }
}
