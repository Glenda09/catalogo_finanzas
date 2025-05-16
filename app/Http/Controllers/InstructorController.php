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
     * Elimina un instructor específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Verificar si el usuario está autenticado
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $instructor = Instructor::find($id);

            if (!$instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Instructor no encontrado'
                ], 404);
            }

            // Verificar si el instructor está asignado a un curso
            $cursoAsignado = Curso::where('id_instructor', $id)->exists();
            if ($cursoAsignado) {
                return response()->json([
                    'message' => 'El instructor está asignado a un curso y no puede ser eliminado'
                ], 400);
            }

            DB::beginTransaction();

            // Desactivar en lugar de eliminar
            $instructor->activo = false;
            $instructor->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Instructor desactivado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al desactivar el instructor: ' . $e->getMessage()
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
}
