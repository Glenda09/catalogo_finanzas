<?php

namespace Tests\Feature;

use App\Models\Usuarios;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = auth('api')->attempt([
            'email' => Usuarios::first()->email ?? Usuarios::factory()->create()->email,
            'password' => 'password' // ajusta si no usas factory
        ]);
    }

    public function test_puede_listar_roles()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->get('/api/roles');

        $response->assertStatus(200);
    }

    public function test_puede_crear_rol()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->post('/api/roles', [
                'nombre' => 'Administrador',
                'descripcion' => 'Acceso completo'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', ['nombre' => 'Administrador']);
    }

    public function test_puede_actualizar_rol()
    {
        $role = Role::create([
            'nombre' => 'Editor',
            'descripcion' => 'Puede editar',
            'creado_por' => Usuarios::first()->id,
            'activo' => true
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->put("/api/roles/{$role->id}", [
                'nombre' => 'Editor Avanzado',
                'descripcion' => 'Permisos extendidos'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('roles', ['nombre' => 'Editor Avanzado']);
    }

    public function test_puede_desactivar_rol()
    {
        $role = Role::create([
            'nombre' => 'Temporal',
            'descripcion' => 'Prueba',
            'creado_por' => Usuarios::first()->id,
            'activo' => true
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer $this->token"])
            ->delete("/api/roles/{$role->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'activo' => false]);
    }
}
