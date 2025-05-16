<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Mostrar todos los roles
    public function index(Request $request)
    {
        // Verificar autenticación del usuario
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Obtener roles con paginación
        $perPage = $request->get('per_page', 10);
        $roles = Role::paginate($perPage);

        return response()->json($roles);
    }

    // Crear un nuevo rol
    public function store(Request $request)
    {
        // Verificar autenticación del usuario
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Validar datos de entrada
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        // Crear un nuevo rol
        $role = Role::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'creado_por' => $user->id,
            'activo' => true,
        ]);

        return response()->json([
            'message' => 'Rol creado correctamente',
            'role' => $role
        ], 201);
    }

    // Actualizar un rol existente
    public function update(Request $request, $id)
    {
        // Verificar autenticación del usuario
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Validar datos de entrada
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        // Buscar y actualizar el rol
        $role = Role::findOrFail($id);
        $role->update($request->only(['nombre', 'descripcion']));

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'role' => $role
        ]);
    }

    // Eliminar un rol
    public function destroy($id)
    {
        // Verificar autenticación del usuario
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Buscar el rol y marcarlo como inactivo
        $role = Role::findOrFail($id);
        $role->update(['activo' => false]);

        return response()->json([
            'message' => 'Rol desactivado correctamente'
        ]);
    }
}
