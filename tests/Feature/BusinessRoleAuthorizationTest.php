<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Negocio $negocio;
    private User $admin;
    private User $usuario;
    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $cliente = Cliente::create([
            'nombre' => 'Cliente de roles',
            'activo' => true,
        ]);

        $this->negocio = Negocio::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Negocio de roles',
            'slug' => 'negocio-roles',
            'activo' => true,
        ]);

        foreach ($this->allModuleRoutes() as $slug => $routeName) {
            $modulo = Modulo::create([
                'nombre' => ucfirst($slug),
                'slug' => $slug,
                'ruta' => $routeName,
                'activo' => true,
            ]);

            $this->negocio->modulos()->attach($modulo->id, [
                'activo' => true,
            ]);
        }

        $this->admin = User::factory()->create(['is_superadmin' => false]);
        $this->usuario = User::factory()->create(['is_superadmin' => false]);
        $this->superadmin = User::factory()->create(['is_superadmin' => true]);

        $this->attachUser($this->admin, 'admin');
        $this->attachUser($this->usuario, 'usuario');
    }

    public function test_admin_puede_administrar_productos_stock_y_compras(): void
    {
        foreach ($this->administrativeRoutes() as $routeName) {
            $this->actingAs($this->admin)
                ->get(route($routeName, $this->negocio))
                ->assertOk();
        }
    }

    public function test_usuario_normal_no_puede_acceder_a_operaciones_administrativas(): void
    {
        foreach ($this->administrativeRoutes() as $routeName) {
            $this->actingAs($this->usuario)
                ->get(route($routeName, $this->negocio))
                ->assertForbidden();
        }
    }

    public function test_usuario_normal_mantiene_acceso_a_ventas_gastos_y_caja(): void
    {
        foreach ($this->operationalRoutes() as $routeName) {
            $this->actingAs($this->usuario)
                ->get(route($routeName, $this->negocio))
                ->assertOk();
        }
    }

    public function test_superadmin_puede_acceder_a_operaciones_administrativas(): void
    {
        foreach ($this->administrativeRoutes() as $routeName) {
            $this->actingAs($this->superadmin)
                ->get(route($routeName, $this->negocio))
                ->assertOk();
        }
    }

    private function attachUser(User $user, string $role): void
    {
        $user->negocios()->attach($this->negocio->id, [
            'rol' => $role,
            'activo' => true,
        ]);
    }

    /** @return list<string> */
    private function administrativeRoutes(): array
    {
        return [
            'gestion.productos.index',
            'gestion.stock.index',
            'gestion.compras.index',
        ];
    }

    /** @return list<string> */
    private function operationalRoutes(): array
    {
        return [
            'gestion.ventas.index',
            'gestion.gastos.index',
            'gestion.caja.index',
        ];
    }

    /** @return array<string, string> */
    private function allModuleRoutes(): array
    {
        return [
            'productos' => 'gestion.productos.index',
            'stock' => 'gestion.stock.index',
            'compras' => 'gestion.compras.index',
            'ventas' => 'gestion.ventas.index',
            'gastos' => 'gestion.gastos.index',
            'caja' => 'gestion.caja.index',
        ];
    }
}
