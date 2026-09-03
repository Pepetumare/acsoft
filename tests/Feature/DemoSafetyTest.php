<?php

namespace Tests\Feature;

use App\Models\DemoProducto;
use App\Models\DemoProveedor;
use App\Models\DemoSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cuota_de_proveedores_se_aplica_por_sesion(): void
    {
        $token = $this->startDemo();
        for ($i = 1; $i <= 20; $i++) {
            DemoProveedor::create(['demo_session_id' => $token, 'nombre' => "Proveedor {$i}"]);
        }

        $this->post(route('demo.proveedores.store'), ['nombre' => 'Exceso'])
            ->assertSessionHasErrors([
                'demo' => 'La demostración alcanzó el límite permitido. Puedes reiniciar la demo o solicitar una demostración completa.',
            ]);
        $this->assertSame(20, DemoProveedor::where('demo_session_id', $token)->count());
    }

    public function test_escrituras_demo_tienen_rate_limit_sin_bloquear_get(): void
    {
        $this->startDemo();

        for ($i = 0; $i < 30; $i++) {
            $this->post(route('demo.proveedores.store'), ['nombre' => "Proveedor {$i}"]);
        }

        $this->post(route('demo.proveedores.store'), ['nombre' => 'Bloqueado'])->assertTooManyRequests();
        $this->get(route('demo.index'))->assertOk();
    }

    public function test_cuotas_de_productos_e_ingresos_se_aplican_por_sesion(): void
    {
        $token = $this->startDemo();
        for ($i = 1; $i <= 40; $i++) {
            DemoProducto::create(['demo_session_id' => $token, 'nombre' => "Producto {$i}", 'unidad' => 'kg']);
        }

        $this->post(route('demo.productos.store'), [])->assertSessionHasErrors('demo');

        $provider = DemoProveedor::create(['demo_session_id' => $token, 'nombre' => 'Proveedor']);
        $product = DemoProducto::where('demo_session_id', $token)->firstOrFail();
        for ($i = 1; $i <= 50; $i++) {
            \App\Models\DemoIngreso::create([
                'demo_session_id' => $token,
                'demo_proveedor_id' => $provider->id,
                'demo_producto_id' => $product->id,
                'fecha' => now(),
                'cantidad_cajas' => 1,
                'peso_total' => 1,
            ]);
        }

        $this->post(route('demo.ingresos.store'), [])->assertSessionHasErrors('demo');
    }

    public function test_dos_demos_no_pueden_ver_ni_modificar_recursos_ajenos(): void
    {
        $first = $this->startDemo();
        $provider = DemoProveedor::create(['demo_session_id' => $first, 'nombre' => 'Privado']);

        $second = $this->newSessionToken();
        $this->withSession(['demo_session_id' => $second])
            ->get(route('demo.proveedores.edit', $provider))->assertNotFound();
        $this->withSession(['demo_session_id' => $second])
            ->delete(route('demo.proveedores.destroy', $provider))->assertNotFound();
        $this->assertDatabaseHas('demo_proveedores', ['id' => $provider->id]);
    }

    public function test_sesion_expirada_recibe_token_nuevo_y_pierde_acceso(): void
    {
        $expired = (string) Str::uuid();
        DemoSession::create(['token' => $expired, 'expires_at' => now()->subMinute()]);
        DemoProveedor::create(['demo_session_id' => $expired, 'nombre' => 'Expirado']);

        $response = $this->withSession(['demo_session_id' => $expired])->get(route('demo.proveedores.index'));

        $response->assertOk()->assertDontSeeText('Expirado');
        $this->assertNotSame($expired, session('demo_session_id'));
    }

    public function test_comando_limpia_solo_sesiones_demo_expiradas(): void
    {
        $expired = (string) Str::uuid();
        $active = (string) Str::uuid();
        DemoSession::create(['token' => $expired, 'expires_at' => now()->subMinute()]);
        DemoSession::create(['token' => $active, 'expires_at' => now()->addHour()]);
        DemoProducto::create(['demo_session_id' => $expired, 'nombre' => 'Viejo', 'unidad' => 'kg']);
        DemoProducto::create(['demo_session_id' => $active, 'nombre' => 'Activo', 'unidad' => 'kg']);

        $this->artisan('demo:cleanup-expired')->assertSuccessful();

        $this->assertDatabaseMissing('demo_sessions', ['token' => $expired]);
        $this->assertDatabaseMissing('demo_productos', ['demo_session_id' => $expired]);
        $this->assertDatabaseHas('demo_sessions', ['token' => $active]);
        $this->assertDatabaseHas('demo_productos', ['demo_session_id' => $active]);
    }

    private function startDemo(): string
    {
        $this->get(route('demo.index'))->assertOk();

        return session('demo_session_id');
    }

    private function newSessionToken(): string
    {
        $token = (string) Str::uuid();
        DemoSession::create(['token' => $token, 'expires_at' => now()->addHours(24)]);

        return $token;
    }
}
