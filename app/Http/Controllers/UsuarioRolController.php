<?php

namespace App\Http\Controllers;

use App\Models\UsuarioRol;
use Illuminate\Http\Request;

class UsuarioRolController extends Controller
{
    // Mostrar todos los registros usuario-rol
    public function index()
    {
        // Validar si el usuario está autenticado
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Obtener todos los registros usuario-rol con sus relaciones
        $usuarioRoles = UsuarioRol::with(['usuario', 'rol'])->get();
        return response()->json($usuarioRoles);
    }

    // Crear una nueva relación usuario-rol
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
        dd($relacion);

        return response()->json([
            'message' => 'Relación usuario-rol creada correctamente',
            'relacion' => $relacion
        ], 201);
    }

    // Actualizar una relación existente
    public function update(Request $request, $id)
    {
        // Validar si el usuario está autenticado
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Validar los datos de entrada
        $request->validate([
            'id_usuario' => 'required|exists:usuarios,id',
            'id_rol' => 'required|exists:roles,id',
        ]);

        // Buscar y actualizar la relación usuario-rol existente
        $relacion = UsuarioRol::findOrFail($id);
        $relacion->update($request->only(['id_usuario', 'id_rol']));

        return response()->json([
            'message' => 'Relación actualizada correctamente',
            'relacion' => $relacion
        ]);
    }

    // Eliminar una relación
    public function destroy($id)
    {
        // Validar si el usuario está autenticado
        $user = auth('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Buscar y eliminar la relación usuario-rol
        $relacion = UsuarioRol::findOrFail($id);
        $relacion->delete();

        return response()->json([
            'message' => 'Relación eliminada correctamente'
        ]);
    }
}
