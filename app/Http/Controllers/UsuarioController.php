<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuarios;
use App\Models\Instructor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $usuarios = Usuarios::with('roles')
            ->paginate($perPage);

        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:usuarios',
                'password' => 'required|string|min:8',
                'telefono' => 'nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Crear nuevo usuario
            $usuario = Usuarios::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => $request->password,
                'telefono' => $request->telefono
            ]);

            // Crear nuevo instructor vinculado al usuario
            $instructor = Instructor::create([
                'id_usuario' => $usuario->id,
                'activo' => true
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Instructor creado exitosamente',
                'data' => [
                    'instructor' => $instructor,
                    'usuario' => $usuario
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el instructor: ' . $e->getMessage()
            ], 500);
        }
    }
}
