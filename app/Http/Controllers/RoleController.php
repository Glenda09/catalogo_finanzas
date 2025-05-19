<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
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

        $perPage = $request->get('per_page', 10);
        $roles = Role::paginate($perPage);

        return response()->json($roles);
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

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

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

    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $role = Role::findOrFail($id);
        $role->update($request->only(['nombre', 'descripcion']));

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'role' => $role
        ]);
    }

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
