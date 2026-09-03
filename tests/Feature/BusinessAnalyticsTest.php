<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Negocio $business;
    private Negocio $otherBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-09-02 12:00:00');
        $client = Cliente::create(['nombre' => 'Cliente analítica', 'activo' => true]);
        $this->business = Negocio::create(['cliente_id' => $client->id, 'nombre' => 'Negocio analítica', 'slug' => 'negocio-analitica', 'activo' => true]);
        $this->otherBusiness = Negocio::create(['cliente_id' => $client->id, 'nombre' => 'Negocio ajeno', 'slug' => 'negocio-ajeno-analitica', 'activo' => true]);
        $this->user = User::factory()->create(['is_superadmin' => false]);
        $this->user->negocios()->attach($this->business->id, ['rol' => 'usuario', 'activo' => true]);
        $analytics = Modulo::create(['nombre' => 'Analítica', 'slug' => 'analitica', 'ruta' => 'gestion.analitica', 'activo' => true]);
        $this->business->modulos()->attach($analytics->id, ['activo' => true]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_usuario_puede_cargar_analitica_de_su_negocio(): void
    {
        $this->actingAs($this->user)->get(route('gestion.analitica', $this->business))
            ->assertOk()->assertSeeText('Analítica')->assertViewHas('kpis')->assertViewHas('charts');
    }

    public function test_usuario_no_puede_ver_analitica_de_otro_negocio(): void
    {
        $this->actingAs($this->user)->get(route('gestion.analitica', $this->otherBusiness))->assertForbidden();
    }

    public function test_periodo_personalizado_filtra_y_aisla_los_datos(): void
    {
        $sale = Venta::create(['negocio_id' => $this->business->id, 'user_id' => $this->user->id, 'fecha' => '2026-08-15', 'total' => 15000, 'metodo_pago' => 'efectivo']);
        $sale->detalles()->create(['descripcion' => 'Café', 'cantidad' => 3, 'precio_unitario' => 5000, 'subtotal' => 15000]);
        Venta::create(['negocio_id' => $this->business->id, 'fecha' => '2026-07-15', 'total' => 99000]);
        Venta::create(['negocio_id' => $this->otherBusiness->id, 'fecha' => '2026-08-15', 'total' => 88000]);
        Gasto::create(['negocio_id' => $this->business->id, 'fecha' => '2026-08-20', 'concepto' => 'Insumos', 'monto' => 4000]);

        $this->actingAs($this->user)->get(route('gestion.analitica', ['negocio' => $this->business, 'periodo' => 'personalizado', 'desde' => '2026-08-01', 'hasta' => '2026-08-31']))
            ->assertOk()->assertViewHas('kpis', fn (array $kpis) => $kpis['ventas_total'] === 15000.0 && $kpis['gastos_total'] === 4000.0 && $kpis['cantidad_ventas'] === 1 && $kpis['productos_vendidos'] === 3.0)
            ->assertViewHas('charts', fn (array $charts) => $charts['products']['labels'] === ['Café']);
    }

    public function test_rango_personalizado_invalido_muestra_error_amigable(): void
    {
        $this->actingAs($this->user)->get(route('gestion.analitica', ['negocio' => $this->business, 'periodo' => 'personalizado', 'desde' => '2026-09-02', 'hasta' => '2026-09-01']))
            ->assertRedirect(route('gestion.analitica', $this->business))->assertSessionHasErrors(['hasta' => 'La fecha hasta debe ser igual o posterior a la fecha desde.']);
    }

    public function test_analitica_responde_correctamente_sin_datos(): void
    {
        $this->actingAs($this->user)->get(route('gestion.analitica', $this->business))
            ->assertOk()->assertSeeText('Aún no hay datos para este período')->assertViewHas('hasActivity', false);
    }

    public function test_muestra_stock_critico_reutilizando_stock_actual(): void
    {
        $product = Producto::create(['negocio_id' => $this->business->id, 'nombre' => 'Café bajo', 'unidad' => 'unidad', 'stock_minimo' => 5, 'activo' => true]);
        StockMovimiento::create(['negocio_id' => $this->business->id, 'producto_id' => $product->id, 'tipo' => 'entrada', 'cantidad' => 2, 'concepto' => 'Inicial']);
        $this->actingAs($this->user)->get(route('gestion.analitica', $this->business))->assertOk()->assertSeeText('Café bajo');
    }

    public function test_usuario_con_un_negocio_aterriza_en_analitica(): void
    {
        $this->assertSame(route('gestion.dashboard', $this->business), $this->user->dashboardUrl());
        $this->actingAs($this->user)->get($this->user->dashboardUrl())
            ->assertRedirect(route('gestion.analitica', $this->business));
    }

    public function test_analitica_inactiva_se_oculta_bloquea_y_landing_usa_ventas(): void
    {
        $analytics = Modulo::where('slug', 'analitica')->firstOrFail();
        $sales = Modulo::create(['nombre' => 'Ventas', 'slug' => 'ventas', 'ruta' => 'gestion.ventas.index', 'activo' => true]);
        $this->business->modulos()->updateExistingPivot($analytics->id, ['activo' => false]);
        $this->business->modulos()->attach($sales->id, ['activo' => true]);

        $this->actingAs($this->user)->get(route('gestion.analitica', $this->business))->assertForbidden();
        $this->actingAs($this->user)->get(route('gestion.dashboard', $this->business))
            ->assertRedirect(route('gestion.ventas.index', $this->business));
        $this->actingAs($this->user)->get(route('gestion.ventas.index', $this->business))
            ->assertOk()->assertDontSee('Analítica');
    }

    public function test_modulo_analitica_globalmente_inactivo_bloquea_acceso(): void
    {
        Modulo::where('slug', 'analitica')->update(['activo' => false]);

        $this->actingAs($this->user)->get(route('gestion.analitica', $this->business))
            ->assertForbidden();
    }
}
