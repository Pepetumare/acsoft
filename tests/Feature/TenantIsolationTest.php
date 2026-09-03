<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private User $usuarioA;
    private User $superadmin;
    private Negocio $negocioA;
    private Negocio $negocioB;

    protected function setUp(): void
    {
        parent::setUp();

        $cliente = Cliente::create([
            'nombre' => 'Cliente de prueba',
            'activo' => true,
        ]);

        $this->negocioA = Negocio::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Negocio A',
            'slug' => 'negocio-a',
            'activo' => true,
        ]);

        $this->negocioB = Negocio::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Negocio B',
            'slug' => 'negocio-b',
            'activo' => true,
        ]);

        foreach ($this->moduleRoutes() as $slug => $routeName) {
            $modulo = Modulo::create([
                'nombre' => ucfirst($slug),
                'slug' => $slug,
                'ruta' => $routeName,
                'activo' => true,
            ]);

            $this->negocioA->modulos()->attach($modulo->id, ['activo' => true]);
            $this->negocioB->modulos()->attach($modulo->id, ['activo' => true]);
        }

        $this->usuarioA = User::factory()->create([
            'is_superadmin' => false,
        ]);

        $this->superadmin = User::factory()->create([
            'is_superadmin' => true,
        ]);

        $this->usuarioA->negocios()->attach($this->negocioA->id, [
            'rol' => 'usuario',
            'activo' => true,
        ]);
    }

    public function test_usuario_a_puede_acceder_al_negocio_a(): void
    {
        $this->actingAs($this->usuarioA)
            ->get(route('gestion.dashboard', $this->negocioA))
            ->assertRedirect(route('gestion.ventas.index', $this->negocioA));
    }

    public function test_usuario_a_no_puede_acceder_a_un_negocio_inactivo(): void
    {
        $this->negocioA->update(['activo' => false]);

        $this->actingAs($this->usuarioA)
            ->get(route('gestion.dashboard', $this->negocioA))
            ->assertForbidden();
    }
    public function test_usuario_a_no_puede_acceder_al_negocio_b(): void
    {
        $this->actingAs($this->usuarioA)
            ->get(route('gestion.dashboard', $this->negocioB))
            ->assertForbidden();
    }

    public function test_usuario_a_no_puede_acceder_a_modulos_del_negocio_b(): void
    {
        foreach ($this->moduleRoutes() as $routeName) {
            $this->actingAs($this->usuarioA)
                ->get(route($routeName, $this->negocioB))
                ->assertForbidden();
        }
    }

    public function test_superadmin_puede_acceder_a_cualquier_negocio(): void
    {
        $this->actingAs($this->superadmin)
            ->get(route('gestion.dashboard', $this->negocioB))
            ->assertRedirect(route('gestion.ventas.index', $this->negocioB));

        foreach ($this->moduleRoutes() as $routeName) {
            $this->actingAs($this->superadmin)
                ->get(route($routeName, $this->negocioB))
                ->assertOk();
        }
    }

    /**
     * @return array<string, string>
     */
    private function moduleRoutes(): array
    {
        return [
            'ventas' => 'gestion.ventas.index',
            'gastos' => 'gestion.gastos.index',
            'stock' => 'gestion.stock.index',
            'caja' => 'gestion.caja.index',
            'compras' => 'gestion.compras.index',
            'reportes' => 'gestion.reportes.index',
        ];
    }
}
