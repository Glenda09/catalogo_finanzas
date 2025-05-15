<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Usuarios;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    /**
     * Muestra un listado de todos los instructores.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $instructores = Instructor::with('usuario')->get();
            return response()->json([
                'status' => 'success',
                'data' => $instructores
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los instructores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Almacena un nuevo instructor en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
                'password' => Hash::make($request->password),
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

    /**
     * Muestra un instructor específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $instructor = Instructor::with('usuario')->find($id);

            if (!$instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $instructor
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener el instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra el formulario para editar un instructor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // En caso de que se requiera una vista para editar instructores
        // Este método podría devolver una vista en aplicaciones web
    }

    /**
     * Actualiza un instructor específico en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $instructor = Instructor::find($id);

            if (!$instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255',
                'apellido' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:usuarios,email,' . $instructor->id_usuario,
                'telefono' => 'nullable|string|max:20',
                'activo' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Actualizar datos del instructor
            if ($request->has('activo')) {
                $instructor->activo = $request->activo;
                $instructor->save();
            }

            // Actualizar datos del usuario asociado
            $usuario = Usuarios::find($instructor->id_usuario);
            if ($usuario) {
                if ($request->has('nombre')) $usuario->nombre = $request->nombre;
                if ($request->has('apellido')) $usuario->apellido = $request->apellido;
                if ($request->has('email')) $usuario->email = $request->email;
                if ($request->has('telefono')) $usuario->telefono = $request->telefono;
                if ($request->has('password') && !empty($request->password)) {
                    $usuario->password = Hash::make($request->password);
                }

                $usuario->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Instructor actualizado exitosamente',
                'data' => [
                    'instructor' => $instructor->fresh(),
                    'usuario' => $usuario
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina un instructor específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $instructor = Instructor::find($id);

            if (!$instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor no encontrado'
                ], 404);
            }

            DB::beginTransaction();

            // Opcional: Desactivar en lugar de eliminar
            // $instructor->activo = false;
            // $instructor->save();

            // Eliminar el instructor
            $instructor->delete();

            // Opcional: Eliminar también el usuario asociado
            // $usuario = Usuarios::find($instructor->id_usuario);
            // if ($usuario) {
            //     $usuario->delete();
            // }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Instructor eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene todos los cursos asociados a un instructor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCursos($id)
    {
        try {
            $instructor = Instructor::find($id);

            if (!$instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor no encontrado'
                ], 404);
            }

            $cursos = Curso::where('id_instructor', $id)
                    ->with('categoria')
                    ->get();

            return response()->json([
                'status' => 'success',
                'data' => $cursos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los cursos del instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambia el estado de activación de un instructor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleEstado($id)
    {
        try {
            $instructor = Instructor::find($id);

            if (!$instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor no encontrado'
                ], 404);
            }

            $instructor->activo = !$instructor->activo;
            $instructor->save();

            $estado = $instructor->activo ? 'activado' : 'desactivado';

            return response()->json([
                'status' => 'success',
                'message' => "Instructor {$estado} exitosamente",
                'data' => $instructor
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cambiar el estado del instructor: ' . $e->getMessage()
            ], 500);
        }
    }
}
