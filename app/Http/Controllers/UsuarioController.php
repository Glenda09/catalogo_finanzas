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
    /**
     * @OA\Get(
     *     path="/api/usuarios",
     *     summary="Listar usuarios del sistema con sus roles",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer"), description="Cantidad por página"),
     *     @OA\Response(response=200, description="Listado de usuarios paginado")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $usuarios = Usuarios::with('roles')
            ->paginate($perPage);

        return response()->json($usuarios);
    }

    /**
     * @OA\Post(
     *     path="/api/usuarios",
     *     summary="Registrar un nuevo usuario e instructor",
     *     tags={"Usuarios"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "apellido", "email", "password"},
     *             @OA\Property(property="nombre", type="string"),
     *             @OA\Property(property="apellido", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="telefono", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuario e instructor creados correctamente"),
     *     @OA\Response(response=422, description="Errores de validación"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
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
