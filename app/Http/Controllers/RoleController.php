<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Listar roles del sistema",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer"), description="Cantidad por página"),
     *     @OA\Response(response=200, description="Listado de roles paginados"),
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

        $perPage = $request->get('per_page', 10);
        $roles = Role::paginate($perPage);

        return response()->json($roles);
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Crear un nuevo rol",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="descripcion", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Rol creado correctamente"),
     *     @OA\Response(response=401, description="Usuario no autenticado"),
     *     @OA\Response(response=422, description="Errores de validación")
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

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Actualizar un rol existente",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID del rol"),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"nombre"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="descripcion", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rol actualizado correctamente"),
     *     @OA\Response(response=401, description="Usuario no autenticado"),
     *     @OA\Response(response=422, description="Errores de validación")
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

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Desactivar un rol",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID del rol"),
     *     @OA\Response(response=200, description="Rol desactivado correctamente"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
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
