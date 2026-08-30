<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class CustomErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);

        Route::get('/_test/error-403', fn () => abort(403, 'SQL secreto interno'));
        Route::get('/_test/error-419', fn () => abort(419));
        Route::get('/_test/error-500', fn () => throw new RuntimeException(
            'SQLSTATE credencial_secreta ruta/interna.php'
        ));
    }

    public function test_404_renderiza_diseno_acsoft_para_invitado(): void
    {
        $this->get('/pagina-que-no-existe')
            ->assertNotFound()
            ->assertSee('Página no encontrada')
            ->assertSee('class="error-card"', false)
            ->assertSee('images/acsoft/logo.svg', false)
            ->assertSee('href="'.route('home').'"', false);
    }

    public function test_403_renderiza_diseno_acsoft_sin_detalle_tecnico(): void
    {
        $this->get('/_test/error-403')
            ->assertForbidden()
            ->assertSee('No tienes permiso para acceder aquí')
            ->assertSee('class="error-card"', false)
            ->assertDontSee('SQL secreto interno');
    }

    public function test_419_usa_vista_personalizada_y_ofrece_nuevo_login(): void
    {
        $this->get('/_test/error-419')
            ->assertStatus(419)
            ->assertSee('Tu sesión ha expirado')
            ->assertSee('Iniciar sesión nuevamente')
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_usuario_autenticado_recibe_enlace_a_dashboard_de_su_negocio(): void
    {
        [$user, $business] = $this->userWithBusiness();

        $this->actingAs($user)
            ->get('/pagina-privada-inexistente')
            ->assertNotFound()
            ->assertSee('href="'.route('gestion.dashboard', $business).'"', false)
            ->assertSee('Volver al dashboard');
    }

    public function test_superadmin_recibe_enlace_a_administracion(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);

        $this->actingAs($superadmin)
            ->get('/admin/pagina-inexistente')
            ->assertNotFound()
            ->assertSee('href="'.route('admin.dashboard').'"', false)
            ->assertSee('Volver a administración');
    }

    public function test_usuario_con_varios_negocios_recibe_selector(): void
    {
        [$user] = $this->userWithBusiness();
        $secondBusiness = $this->createBusiness('Negocio secundario', 'negocio-secundario');
        $secondBusiness->usuarios()->attach($user->id, ['rol' => 'usuario', 'activo' => true]);

        $this->actingAs($user)
            ->get('/pagina-sin-contexto')
            ->assertNotFound()
            ->assertSee('href="'.route('business.select').'"', false)
            ->assertSee('Seleccionar negocio');
    }

    public function test_error_500_no_expone_informacion_sensible(): void
    {
        $this->get('/_test/error-500')
            ->assertStatus(500)
            ->assertSee('Ocurrió un error inesperado')
            ->assertDontSee('SQLSTATE')
            ->assertDontSee('credencial_secreta')
            ->assertDontSee('ruta/interna.php');
    }

    /** @return array{User, Negocio} */
    private function userWithBusiness(): array
    {
        $user = User::factory()->create(['is_superadmin' => false]);
        $business = $this->createBusiness('Negocio errores', 'negocio-errores');
        $business->usuarios()->attach($user->id, ['rol' => 'usuario', 'activo' => true]);

        return [$user, $business];
    }

    private function createBusiness(string $name, string $slug): Negocio
    {
        $client = Cliente::create(['nombre' => 'Cliente '.$name, 'activo' => true]);

        return Negocio::create([
            'cliente_id' => $client->id,
            'nombre' => $name,
            'slug' => $slug,
            'activo' => true,
        ]);
    }
}
