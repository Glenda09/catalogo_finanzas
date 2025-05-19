<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Modulo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller
{
    // Listar todos los módulos
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

    // Crear un nuevo módulo
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

    // Mostrar un módulo específico
    public function show($id)
    {
        $modulo = Modulo::findOrFail($id);
        return response()->json($modulo);
    }

    // Actualizar un módulo
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

    // Eliminar un módulo
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

        $modulo->activo = false;
        $modulo->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Módulo eliminado exitosamente'
        ], 200);
    }
}
