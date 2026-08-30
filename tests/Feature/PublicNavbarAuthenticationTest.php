<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PublicNavbarAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_ve_enlace_para_iniciar_sesion(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee('href="' . route('login') . '"', false)
            ->assertDontSee('>Dashboard</a>', false);
    }

    public function test_superadmin_ve_dashboard_con_destino_administrativo(): void
    {
        $user = User::factory()->create(['is_superadmin' => true]);

        $response = $this->actingAs($user)
            ->get(route('home'))
            ->assertOk();

        $this->assertDashboardLink($response, route('admin.dashboard'));
    }

    public function test_usuario_con_un_negocio_activo_ve_dashboard_del_negocio(): void
    {
        $user = User::factory()->create(['is_superadmin' => false]);
        $negocio = $this->createActiveBusiness('negocio-unico');
        $this->attachActiveBusiness($user, $negocio);

        $response = $this->actingAs($user)
            ->get(route('functions'))
            ->assertOk();

        $this->assertDashboardLink($response, route('gestion.dashboard', $negocio));
    }

    public function test_usuario_con_varios_negocios_activos_ve_selector_de_negocio(): void
    {
        $user = User::factory()->create(['is_superadmin' => false]);
        $this->attachActiveBusiness($user, $this->createActiveBusiness('negocio-uno'));
        $this->attachActiveBusiness($user, $this->createActiveBusiness('negocio-dos'));

        $response = $this->actingAs($user)
            ->get(route('pricing'))
            ->assertOk();

        $this->assertDashboardLink($response, route('business.select'));
    }

    public function test_usuario_sin_negocios_activos_ve_dashboard_hacia_cuenta_sin_negocio(): void
    {
        $user = User::factory()->create(['is_superadmin' => false]);

        $response = $this->actingAs($user)
            ->get(route('contacto'))
            ->assertOk();

        $this->assertDashboardLink($response, route('account.no-business'));
    }

    private function createActiveBusiness(string $slug): Negocio
    {
        $cliente = Cliente::create([
            'nombre' => 'Cliente ' . $slug,
            'activo' => true,
        ]);

        return Negocio::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Negocio ' . $slug,
            'slug' => $slug,
            'activo' => true,
        ]);
    }

    private function attachActiveBusiness(User $user, Negocio $negocio): void
    {
        $user->negocios()->attach($negocio->id, [
            'rol' => 'usuario',
            'activo' => true,
        ]);
    }

    private function assertDashboardLink(TestResponse $response, string $url): void
    {
        $response
            ->assertSee('Dashboard')
            ->assertSee('href="' . $url . '"', false);
    }
}
