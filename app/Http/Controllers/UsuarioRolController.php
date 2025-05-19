<?php

namespace App\Http\Controllers;

use App\Models\UsuarioRol;
use Illuminate\Http\Request;

class UsuarioRolController extends Controller
{
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
