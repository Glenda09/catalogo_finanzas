<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modulo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/modulos",
     *     summary="Listar módulos con filtros opcionales",
     *     tags={"Módulos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_curso", in="query", @OA\Schema(type="integer"), description="Filtrar por ID del curso"),
     *     @OA\Parameter(name="nombre", in="query", @OA\Schema(type="string"), description="Filtrar por nombre del módulo"),
     *     @OA\Parameter(name="activo", in="query", @OA\Schema(type="boolean"), description="Filtrar por estado activo/inactivo"),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer"), description="Cantidad por página"),
     *     @OA\Response(response=200, description="Listado de módulos")
     * )
     */
    public function index(Request $request)
    {
        $query = Modulo::query();

        // Filtros opcionales
        if ($request->has('id_curso')) {
            $query->where('id_curso', $request->input('id_curso'));
        }
        if ($request->has('nombre')) {
            $query->where('nombre', 'like', '%' . $request->input('nombre') . '%');
        }
        if ($request->has('activo')) {
            $query->where('activo', $request->input('activo'));
        }

        // Paginación
        $perPage = $request->input('per_page', 10);
        $modulos = $query->paginate($perPage);

        return response()->json($modulos);
    }

    /**
     * @OA\Post(
     *     path="/api/modulos",
     *     summary="Crear un nuevo módulo",
     *     tags={"Módulos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_curso", "nombre", "fecha_fin"},
     *             @OA\Property(property="id_curso", type="integer"),
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="fecha_fin", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Módulo creado exitosamente"),
     *     @OA\Response(response=422, description="Errores de validación"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_curso' => 'required|integer',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_fin' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $modulo = Modulo::create([
                'id_curso' => $request->input('id_curso'),
                'nombre' => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
                'fecha_inicio' => now(),
                'fecha_fin' => $request->input('fecha_fin'),
                'activo' => true,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Módulo creado exitosamente',
                'modulo' => $modulo
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el módulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/modulos/{id}",
     *     summary="Obtener un módulo por ID",
     *     tags={"Módulos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID del módulo"),
     *     @OA\Response(response=200, description="Módulo encontrado")
     * )
     */
    public function show($id)
    {
        $modulo = Modulo::findOrFail($id);
        return response()->json($modulo);
    }

    /**
     * @OA\Put(
     *     path="/api/modulos/{id}",
     *     summary="Actualizar un módulo existente",
     *     tags={"Módulos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID del módulo"),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="id_curso", type="integer"),
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="descripcion", type="string"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date"),
     *             @OA\Property(property="fecha_fin", type="string", format="date"),
     *             @OA\Property(property="activo", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Módulo actualizado exitosamente"),
     *     @OA\Response(response=404, description="Módulo no encontrado"),
     *     @OA\Response(response=422, description="Errores de validación"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'id_curso' => 'sometimes|required|integer',
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'sometimes|required|date',
            'activo' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $modulo = Modulo::findOrFail($id);

        if (!$modulo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Módulo no encontrado'
            ], 404);
        }

        $modulo->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Módulo actualizado exitosamente',
            'modulo' => $modulo
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/modulos/{id}",
     *     summary="Eliminar lógicamente un módulo",
     *     tags={"Módulos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID del módulo"),
     *     @OA\Response(response=200, description="Módulo eliminado exitosamente"),
     *     @OA\Response(response=409, description="Módulo no se puede eliminar por estar asignado a inscripciones"),
     *     @OA\Response(response=404, description="Módulo no encontrado"),
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

        $modulo = Modulo::findOrFail($id);

        if (!$modulo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Módulo no encontrado'
            ], 404);
        }

        // Verificar si el curso al que pertenece el módulo está asignado a alguna inscripción
        $inscripcionCurso = DB::table('inscripcions')
            ->where('id_curso', $modulo->id_curso)
            ->exists();

        if ($inscripcionCurso) {
            return response()->json([
                'message' => 'No se puede eliminar el módulo porque está asignado a una inscripción'
            ], 409);
        }

        $modulo->activo = false;
        $modulo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Módulo eliminado exitosamente'
        ], 200);
    }
}
