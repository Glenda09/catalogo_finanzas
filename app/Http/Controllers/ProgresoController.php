<?php

namespace App\Http\Controllers;

use App\Models\Progreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgresoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/progresos",
     *     summary="Listar progresos con filtros opcionales",
     *     tags={"Progresos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id_inscripcion", in="query", @OA\Schema(type="integer"), description="Filtrar por ID de inscripción"),
     *     @OA\Parameter(name="id_modulo", in="query", @OA\Schema(type="integer"), description="Filtrar por ID de módulo"),
     *     @OA\Parameter(name="completado", in="query", @OA\Schema(type="boolean"), description="Filtrar por completado (true/false)"),
     *     @OA\Parameter(name="activo", in="query", @OA\Schema(type="boolean"), description="Filtrar por estado activo/inactivo"),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer"), description="Cantidad por página"),
     *     @OA\Response(response=200, description="Listado de progresos"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $query = Progreso::query();

        // Filtros opcionales
        if ($request->has('id_inscripcion')) {
            $query->where('id_inscripcion', $request->input('id_inscripcion'));
        }
        if ($request->has('id_modulo')) {
            $query->where('id_modulo', $request->input('id_modulo'));
        }
        if ($request->has('completado')) {
            $query->where('completado', $request->input('completado'));
        }
        if ($request->has('activo')) {
            $query->where('activo', $request->input('activo'));
        }

        $perPage = $request->input('per_page', 15);
        $progresos = $query->paginate($perPage);

        return response()->json($progresos);
    }

    /**
     * @OA\Post(
     *     path="/api/progresos",
     *     summary="Registrar un nuevo progreso",
     *     tags={"Progresos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_inscripcion", "id_modulo", "fecha_inicio"},
     *             @OA\Property(property="id_inscripcion", type="integer"),
     *             @OA\Property(property="id_modulo", type="integer"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date"),
     *             @OA\Property(property="calificacion", type="number", format="float"),
     *             @OA\Property(property="fecha_fin", type="string", format="date"),
     *             @OA\Property(property="tiempo_total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Progreso creado exitosamente"),
     *     @OA\Response(response=422, description="Errores de validación"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'id_inscripcion' => 'required|exists:inscripcions,id',
            'id_modulo' => 'required|exists:modulos,id',
            'fecha_inicio' => 'required|date',
            // 'completado' y 'activo' no se validan porque se asignan por defecto
            'calificacion' => 'nullable|numeric|min:0|max:100',
            'fecha_fin' => 'nullable|date',
            'tiempo_total' => 'nullable|integer|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $request->all();
        $data['activo'] = true;
        $data['completado'] = false;
        $progreso = Progreso::create($data);
        return response()->json($progreso, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/progresos/{id}",
     *     summary="Actualizar un progreso existente",
     *     tags={"Progresos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID del progreso"),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="id_inscripcion", type="integer"),
     *             @OA\Property(property="id_modulo", type="integer"),
     *             @OA\Property(property="fecha_inicio", type="string", format="date"),
     *             @OA\Property(property="completado", type="boolean"),
     *             @OA\Property(property="calificacion", type="number", format="float"),
     *             @OA\Property(property="fecha_fin", type="string", format="date"),
     *             @OA\Property(property="tiempo_total", type="integer"),
     *             @OA\Property(property="activo", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Progreso actualizado exitosamente"),
     *     @OA\Response(response=404, description="Progreso no encontrado"),
     *     @OA\Response(response=422, description="Errores de validación"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        $response = null;
        if (!$user) {
            $response = response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        } else {
            $progreso = Progreso::find($id);
            if (!$progreso) {
                $response = response()->json([
                    'status' => 'error',
                    'message' => 'Progreso no encontrado'
                ], 404);
            } else {
                $validator = Validator::make($request->all(), [
                    'id_inscripcion' => 'sometimes|exists:inscripcions,id',
                    'id_modulo' => 'sometimes|exists:modulos,id',
                    'fecha_inicio' => 'sometimes|date',
                    'completado' => 'sometimes|boolean',
                    'calificacion' => 'nullable|numeric|min:0|max:100',
                    'fecha_fin' => 'nullable|date',
                    'tiempo_total' => 'nullable|integer|min:0',
                    'activo' => 'sometimes|boolean',
                ]);
                if ($validator->fails()) {
                    $response = response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors()
                    ], 422);
                } else {
                    $progreso->update($request->all());
                    $response = response()->json($progreso);
                }
            }
        }
        return $response;
    }

    /**
     * @OA\Delete(
     *     path="/api/progresos/{id}",
     *     summary="Eliminar un progreso",
     *     tags={"Progresos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID del progreso"),
     *     @OA\Response(response=200, description="Progreso eliminado correctamente"),
     *     @OA\Response(response=404, description="Progreso no encontrado"),
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
        $progreso = Progreso::find($id);
        if (!$progreso) {
            return response()->json([
                'status' => 'error',
                'message' => 'Progreso no encontrado'
            ], 404);
        }
        $progreso->delete();
        return response()->json(['status' => 'success', 'message' => 'Progreso eliminado']);
    }
}
