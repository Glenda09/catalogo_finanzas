<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Curso;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CursoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cursos",
     *     summary="Listar cursos activos",
     *     security={{"bearerAuth":{}}},
     *     tags={"Cursos"},
     *     @OA\Parameter(
     *         name="categoria",
     *         in="query",
     *         description="Filtrar por nombre de categoría",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="titulo",
     *         in="query",
     *         description="Filtrar por título del curso",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Cantidad de resultados por página",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Listado paginado de cursos")
     * )
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
     * @OA\Post(
     *     path="/api/cursos",
     *     summary="Crear un nuevo curso",
     *     security={{"bearerAuth":{}}},
     *     tags={"Cursos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo","descripcion","precio","duracion","id_instructor","id_categoria"},
     *             @OA\Property(property="titulo", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="precio", type="number", format="float"),
     *             @OA\Property(property="duracion", type="integer"),
     *             @OA\Property(property="id_instructor", type="integer"),
     *             @OA\Property(property="id_categoria", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Curso creado exitosamente"),
     *     @OA\Response(response=422, description="Errores de validación"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
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
                'fecha_fin_vigencia' => 'required|date|after:fecha_incio_vigencia',
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
                'fecha_inicio_vigencia' => now(),
                'fecha_fin_vigencia' => $request->fecha_fin_vigencia,
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
     * @OA\Get(
     *     path="/api/cursos/{id}",
     *     summary="Obtener detalles de un curso",
     *     security={{"bearerAuth":{}}},
     *     tags={"Cursos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del curso",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Detalles del curso"),
     *     @OA\Response(response=404, description="Curso no encontrado")
     * )
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
     * @OA\Put(
     *     path="/api/cursos/{id}",
     *     summary="Actualizar un curso existente",
     *     security={{"bearerAuth":{}}},
     *     tags={"Cursos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del curso a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulo", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="precio", type="number", format="float"),
     *             @OA\Property(property="duracion", type="integer"),
     *             @OA\Property(property="id_instructor", type="integer"),
     *             @OA\Property(property="id_categoria", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Curso actualizado"),
     *     @OA\Response(response=404, description="Curso no encontrado"),
     *     @OA\Response(response=401, description="Usuario no autenticado"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
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
     * @OA\Delete(
     *     path="/api/cursos/{id}",
     *     summary="Desactivar un curso",
     *     security={{"bearerAuth":{}}},
     *     tags={"Cursos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del curso a desactivar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Curso desactivado correctamente"),
     *     @OA\Response(response=404, description="Curso no encontrado"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
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
