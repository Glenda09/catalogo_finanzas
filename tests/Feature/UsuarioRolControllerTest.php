<?php

namespace Tests\Feature;

use App\Models\Usuarios;
use App\Models\Role;
use App\Models\UsuarioRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioRolControllerTest extends TestCase
{
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = auth('api')->attempt([
            'email' => Usuarios::first()->email ?? Usuarios::factory()->create()->email,
            'password' => 'password'
        ]);
    }

    public function test_puede_listar_usuario_roles()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->get('/api/usuario_rol');

        $response->assertStatus(200);
    }

    public function test_puede_crear_usuario_rol()
    {
        $usuario = Usuarios::first() ?? Usuarios::factory()->create();
        $rol = Role::create(['nombre' => 'Tester', 'descripcion' => '', 'creado_por' => $usuario->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->post('/api/usuario_rol', [
                'id_usuario' => $usuario->id,
                'id_rol' => $rol->id
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('usuario_rol', [
            'id_usuario' => $usuario->id,
            'id_rol' => $rol->id
        ]);
    }

    public function test_puede_actualizar_usuario_rol()
    {
        $usuario = Usuarios::first() ?? Usuarios::factory()->create();
        $rol1 = Role::create(['nombre' => 'Tester 1', 'descripcion' => '', 'creado_por' => $usuario->id]);
        $rol2 = Role::create(['nombre' => 'Tester 2', 'descripcion' => '', 'creado_por' => $usuario->id]);

        $relacion = UsuarioRol::create([
            'id_usuario' => $usuario->id,
            'id_rol' => $rol1->id
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->put("/api/usuario_rol/{$relacion->id}", [
                'id_usuario' => $usuario->id,
                'id_rol' => $rol2->id
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('usuario_rol', ['id' => $relacion->id, 'id_rol' => $rol2->id]);
    }

    public function test_puede_eliminar_usuario_rol()
    {
        $usuario = Usuarios::first() ?? Usuarios::factory()->create();
        $rol = Role::create(['nombre' => 'Eliminar', 'descripcion' => '', 'creado_por' => $usuario->id]);

        $relacion = UsuarioRol::create([
            'id_usuario' => $usuario->id,
            'id_rol' => $rol->id
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->delete("/api/usuario_rol/{$relacion->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('usuario_rol', ['id' => $relacion->id]);
    }
}
