<?php

namespace App\Http\Controllers;

use App\Models\Progreso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgresoController extends Controller
{
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
