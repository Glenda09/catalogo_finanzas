<?php

namespace App\Http\Controllers;

use App\Models\UsuarioRol;
use Illuminate\Http\Request;

class UsuarioRolController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/usuario-roles",
     *     summary="Listar todas las relaciones usuario-rol",
     *     tags={"UsuarioRol"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Listado de relaciones"),
     *     @OA\Response(response=401, description="Usuario no autenticado")
     * )
     */
    public function index()
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $usuarioRoles = UsuarioRol::with(['usuario', 'rol'])->get();
        return response()->json($usuarioRoles);
    }

    /**
     * @OA\Post(
     *     path="/api/usuario-roles",
     *     summary="Asignar un rol a un usuario",
     *     tags={"UsuarioRol"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_usuario", "id_rol"},
     *             @OA\Property(property="id_usuario", type="integer"),
     *             @OA\Property(property="id_rol", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Relación creada correctamente"),
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
            'id_usuario' => 'required|exists:usuarios,id',
            'id_rol' => 'required|exists:roles,id',
        ]);

        $relacion = UsuarioRol::create([
            'id_usuario' => $request->id_usuario,
            'id_rol' => $request->id_rol,
        ]);

        return response()->json([
            'message' => 'Relación usuario-rol creada correctamente',
            'relacion' => $relacion
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/usuario-roles/{id}",
     *     summary="Actualizar una relación usuario-rol",
     *     tags={"UsuarioRol"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID de la relación"),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"id_usuario", "id_rol"},
     *             @OA\Property(property="id_usuario", type="integer"),
     *             @OA\Property(property="id_rol", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Relación actualizada correctamente"),
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
            'id_usuario' => 'required|exists:usuarios,id',
            'id_rol' => 'required|exists:roles,id',
        ]);

        $relacion = UsuarioRol::findOrFail($id);
        $relacion->update($request->only(['id_usuario', 'id_rol']));

        return response()->json([
            'message' => 'Relación actualizada correctamente',
            'relacion' => $relacion
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/usuario-roles/{id}",
     *     summary="Eliminar una relación usuario-rol",
     *     tags={"UsuarioRol"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="ID de la relación"),
     *     @OA\Response(response=200, description="Relación eliminada correctamente"),
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

        $relacion = UsuarioRol::findOrFail($id);
        $relacion->delete();

        return response()->json([
            'message' => 'Relación eliminada correctamente'
        ]);
    }
}
