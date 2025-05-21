<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Categoria;
use App\Models\Instructor;
use App\Models\Usuarios;
use App\Services\LoginTestService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CursoControllerTest extends TestCase
{
    protected $token;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $this->authenticateAndGetToken();
    }

    protected function authenticateAndGetToken()
    {
        $loginService = new LoginTestService();

        return $loginService->login([
            "permisos" => [
                'GESTION_CURSOS',
            ],
        ]);
    }

    /** @test */
    public function test_puede_listar_cursos()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/cursos');

        if ($response->status() === 401) {
            $this->fail('No existe un usuario con los permisos necesarios');
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function test_puede_crear_curso()
    {
        $usuarioInstructor = Usuarios::create([
            'nombre' => 'Profe',
            'apellido' => 'Token',
            'email' => 'profe_token@example.com',
            'password' => 'password'
        ]);

        $instructor = Instructor::create(['id_usuario' => $usuarioInstructor->id, 'activo' => true]);
        $categoria = Categoria::create(['nombre' => 'TokenCat', 'descripcion' => 'Test', 'activo' => true]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('/api/cursos', [
            'titulo' => 'Curso API Token',
            'descripcion' => 'Test con token',
            'precio' => 100,
            'duracion' => 8,
            'fecha_fin_vigencia' => now()->addDays(30)->format('Y-m-d'),
            'id_instructor' => $instructor->id,
            'id_categoria' => $categoria->id,
        ]);

        if ($response->status() === 401) {
            $this->fail('No existe un usuario con los permisos necesarios');
        }

        $response->assertStatus(201);
        $cursoId = $response->json('curso.id');

        Curso::find($cursoId)?->forceDelete();
    }

    /** @test */
    public function test_puede_mostrar_detalle_de_curso()
    {
        $usuarioInstructor = Usuarios::create([
            'nombre' => 'Show',
            'apellido' => 'Test',
            'email' => 'show@example.com',
            'password' => 'password'
        ]);
        $instructor = Instructor::create(['id_usuario' => $usuarioInstructor->id, 'activo' => true]);
        $categoria = Categoria::create(['nombre' => 'ShowTest', 'descripcion' => 'Ver curso', 'activo' => true]);
        $usuarioCreador = Usuarios::create([
            'nombre' => 'Creador',
            'apellido' => 'Test',
            'email' => 'creador@example.com',
            'password' => 'password'
        ]);

        $curso = Curso::create([
            'titulo' => 'Curso Detalle',
            'descripcion' => 'Detalle test',
            'precio' => 50,
            'duracion' => 10,
            'fecha_inicio_vigencia' => now(),
            'fecha_fin_vigencia' => now()->addDays(15),
            'id_instructor' => $instructor->id,
            'id_categoria' => $categoria->id,
            'creado_por' => $usuarioCreador->id,
            'activo' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/cursos/' . $curso->id);

        $response->assertStatus(200);
        $curso->forceDelete();
    }

    /** @test */
    public function test_puede_actualizar_curso()
    {
        // Crear todo el entorno
        $usuarioInstructor = Usuarios::create(['nombre' => 'Upd', 'apellido' => 'Test', 'email' => 'upd@example.com', 'password' => bcrypt('12345678')]);
        $instructor = Instructor::create(['id_usuario' => $usuarioInstructor->id, 'activo' => true]);
        $categoria = Categoria::create(['nombre' => 'UpdCat', 'descripcion' => 'CatUpd', 'activo' => true]);
        $creador = Usuarios::create(['nombre' => 'UpdUser', 'apellido' => 'Token', 'email' => 'updadmin@example.com', 'password' => bcrypt('12345678')]);

        $curso = Curso::create([
            'titulo' => 'Viejo título',
            'descripcion' => 'Vieja desc',
            'precio' => 20,
            'duracion' => 6,
            'fecha_inicio_vigencia' => now(),
            'fecha_fin_vigencia' => now()->addDays(20),
            'id_instructor' => $instructor->id,
            'id_categoria' => $categoria->id,
            'creado_por' => $creador->id,
            'activo' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->put('/api/cursos/' . $curso->id, [
            'titulo' => 'Título actualizado',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cursos', [
            'id' => $curso->id,
            'titulo' => 'Título actualizado',
        ]);

        $curso->forceDelete();
    }

    /** @test */
    public function test_puede_desactivar_curso()
    {
        $usuarioInstructor = Usuarios::create([
            'nombre' => 'Del',
            'apellido' => 'Test',
            'email' => 'del@example.com',
            'password' => 'password'
        ]);
        $instructor = Instructor::create(['id_usuario' => $usuarioInstructor->id, 'activo' => true]);
        $categoria = Categoria::create(['nombre' => 'DelCat', 'descripcion' => 'Eliminar curso', 'activo' => true]);
        $usuarioCreador = Usuarios::create([
            'nombre' => 'Carlos',
            'apellido' => 'Admin',
            'email' => 'admin_delete@example.com',
            'password' => 'password'
        ]);

        $curso = Curso::create([
            'titulo' => 'Curso a borrar',
            'descripcion' => 'Curso de prueba',
            'precio' => 75,
            'duracion' => 12,
            'fecha_inicio_vigencia' => now(),
            'fecha_fin_vigencia' => now()->addDays(30),
            'id_instructor' => $instructor->id,
            'id_categoria' => $categoria->id,
            'creado_por' => $usuarioCreador->id,
            'activo' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->delete('/api/cursos/' . $curso->id);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cursos', [
            'id' => $curso->id,
            'activo' => false,
        ]);

        $curso->forceDelete();
    }
}
