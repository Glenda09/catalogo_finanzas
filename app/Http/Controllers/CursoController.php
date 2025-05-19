<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
        $query = Curso::where('activo', true);

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
        try {
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'duracion' => 'required|integer|min:1',
                'id_instructor' => 'required|exists:instructores,id',
                'id_categoria' => 'required|exists:categorias,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $curso = Curso::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'duracion_horas' => $request->duracion,
                'id_instructor' => $request->id_instructor,
                'id_categoria' => $request->id_categoria,
                'creado_por' => $user->id,
                'activo' => true,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Curso creado exitosamente',
                'curso' => $curso
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el curso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra un recurso específico.
     *
     * Este método recupera y devuelve un curso específico basado en su ID.
     * Generalmente se utiliza para mostrar los detalles de un curso en una vista.
     *
     * @param int $id El ID del curso a mostrar.
     * @return \Illuminate\Http\Response Respuesta HTTP con los detalles del curso.
     */
    public function show($id)
    {
        $curso = Curso::with(['instructor.usuario', 'categoria'])->find($id);

        if (!$curso) {
            return response()->json([
                'status' => 'error',
                'message' => 'Curso no encontrado'
            ], 404);
        }

        return response()->json([
            'curso' => $curso,
            'instructor_nombre' => $curso->instructor->usuario->nombre ?? null,
            'instructor_apellido' => $curso->instructor->usuario->apellido ?? null,
        ]);
    }

    /**
     * Actualiza un recurso específico.
     *
     * Este método toma los datos de entrada y actualiza un curso existente en el sistema.
     * Asegúrate de validar los parámetros antes de llamar a este método.
     *
     * @param int $id El ID del curso a actualizar.
     * @param \Illuminate\Http\Request $request Los datos de entrada para la actualización.
     * @return \Illuminate\Http\Response Respuesta HTTP con el resultado de la operación.
     */
    public function update($id, Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string',
            'precio' => 'sometimes|required|numeric|min:0',
            'duracion' => 'sometimes|required|integer|min:1',
            'id_instructor' => 'sometimes|required|exists:instructores,id',
            'id_categoria' => 'sometimes|required|exists:categorias,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $curso = Curso::find($id);

        if (!$curso) {
            return response()->json([
                'status' => 'error',
                'message' => 'Curso no encontrado'
            ], 404);
        }

        $curso->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Curso actualizado exitosamente',
            'curso' => $curso
        ]);
    }

    /**
     * Desactiva un recurso específico.
     *
     * Este método marca un curso existente como inactivo en lugar de eliminarlo físicamente.
     * Generalmente se utiliza para desactivar un curso en el sistema.
     *
     * @param int $id El ID del curso a desactivar.
     * @return \Illuminate\Http\Response Respuesta HTTP con el resultado de la operación.
     */
    public function destroy($id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $curso = Curso::find($id);

        if (!$curso) {
            return response()->json([
                'status' => 'error',
                'message' => 'Curso no encontrado'
            ], 404);
        }

        $curso->activo = false;
        $curso->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Curso desactivado exitosamente'
        ]);
    }
}
