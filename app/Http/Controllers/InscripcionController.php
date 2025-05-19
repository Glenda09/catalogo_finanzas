<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Progreso;
use App\Models\Modulo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InscripcionController extends Controller
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

        $query = Inscripcion::with(['usuario', 'curso'])
            ->where('activo', true);

        // Filtros opcionales
        if ($request->has('id_usuario')) {
            $query->where('id_usuario', $request->id_usuario);
        }
        if ($request->has('id_curso')) {
            $query->where('id_curso', $request->id_curso);
        }
        if ($request->has('fecha_inscripcion')) {
            $query->whereDate('fecha_inscripcion', $request->fecha_inscripcion);
        }

        $perPage = $request->get('per_page', 10);
        $inscripciones = $query->paginate($perPage);

        return response()->json($inscripciones);
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
            'id_curso' => 'required|exists:cursos,id',
        ]);

        // Validar que el curso esté vigente
        $curso = Curso::where('id', $request->id_curso)
            ->where('fecha_fin_vigencia', '>=', now())
            ->where('activo', true)
            ->first();

        if (!$curso) {
            return response()->json([
                'status' => 'error',
                'message' => 'Curso no encontrado o inactivo'
            ], 404);
        }

        if ($curso->fecha_fin_vigencia && now()->gt($curso->fecha_fin_vigencia)) {
            return response()->json([
                'status' => 'error',
                'message' => 'El curso ya no está vigente'
            ], 400);
        }

        return DB::transaction(function () use ($request) {
            $inscripcion = Inscripcion::create([
                'id_usuario' => $request->id_usuario,
                'id_curso' => $request->id_curso,
                'fecha_inscripcion' => now(),
                'activo' => true,
            ]);

            // Buscar el primer módulo activo del curso
            $modulo = Modulo::where('id_curso', $request->id_curso)
                ->where('activo', true)
                ->orderBy('id')
                ->first();

            if ($modulo) {
                Progreso::create([
                    'id_inscripcion' => $inscripcion->id,
                    'id_modulo' => $modulo->id,
                    'fecha_inicio' => now(),
                    'completado' => false,
                    'activo' => true,
                ]);
            }

            return response()->json($inscripcion, 201);
        });
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

        $inscripcion = Inscripcion::where('id', $id)->where('activo', true)->first();
        if (!$inscripcion) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inscripción no encontrada'
            ], 404);
        }

        $inscripcion->update($request->only(['id_usuario', 'id_curso', 'fecha_inscripcion']));
        return response()->json($inscripcion);
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

        $inscripcion = Inscripcion::where('id', $id)->where('activo', true)->first();
        if (!$inscripcion) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inscripción no encontrada'
            ], 404);
        }

        $inscripcion->activo = false;
        $inscripcion->save();
        return response()->json(['status' => 'success', 'message' => 'Inscripción eliminada lógicamente']);
    }
}
