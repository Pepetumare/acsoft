<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class PurchaseReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Negocio $business;
    private Negocio $otherBusiness;
    private Producto $product;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Cliente::create([
            'nombre' => 'Cliente compras y reportes',
            'activo' => true,
        ]);

        $this->business = Negocio::create([
            'cliente_id' => $client->id,
            'nombre' => 'Negocio compras',
            'slug' => 'negocio-compras',
            'activo' => true,
        ]);

        $this->otherBusiness = Negocio::create([
            'cliente_id' => $client->id,
            'nombre' => 'Negocio ajeno',
            'slug' => 'negocio-ajeno-compras',
            'activo' => true,
        ]);

        foreach (['compras', 'stock', 'reportes'] as $slug) {
            $module = Modulo::create([
                'nombre' => ucfirst($slug),
                'slug' => $slug,
                'activo' => true,
            ]);

            $this->business->modulos()->attach($module->id, ['activo' => true]);
        }

        $this->admin = User::factory()->create(['is_superadmin' => false]);
        $this->admin->negocios()->attach($this->business->id, [
            'rol' => 'admin',
            'activo' => true,
        ]);

        $this->product = $this->business->productos()->create([
            'nombre' => 'Producto compra',
            'codigo' => 'COMPRA-1',
            'unidad' => 'unidad',
            'precio_venta' => 1000,
            'stock_minimo' => 0,
            'activo' => true,
        ]);
    }

    public function test_compra_reutiliza_producto_repetido_y_agrupa_entrada_stock(): void
    {
        $this->postPurchase([
            $this->detail($this->product->id, 2, 100),
            $this->detail($this->product->id, 3, 200),
        ])->assertRedirect(route('gestion.compras.index', $this->business));

        $this->assertDatabaseCount('compras', 1);
        $this->assertDatabaseCount('compra_detalles', 2);
        $this->assertDatabaseHas('compras', [
            'negocio_id' => $this->business->id,
            'total' => 800,
        ]);
        $this->assertDatabaseHas('stock_movimientos', [
            'producto_id' => $this->product->id,
            'tipo' => 'entrada',
            'cantidad' => 5,
            'origen_tipo' => 'compra',
        ]);
        $this->assertSame(1, StockMovimiento::where('origen_tipo', 'compra')->count());
    }

    public function test_compra_de_diez_unidades_es_permitida(): void
    {
        $this->postPurchase([
            $this->detail($this->product->id, 10, 100),
        ])->assertRedirect(route('gestion.compras.index', $this->business));

        $this->assertDatabaseHas('compra_detalles', [
            'producto_id' => $this->product->id,
            'cantidad' => 10,
        ]);
    }

    public function test_compra_de_diez_coma_cinco_unidades_es_rechazada(): void
    {
        $this->postPurchase([
            $this->detail($this->product->id, 10.5, 100),
        ])->assertSessionHasErrors([
            'detalles.0.cantidad' => 'La cantidad para productos medidos en unidad debe ser un número entero mayor o igual a 1.',
        ]);

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('compra_detalles', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_compra_no_crea_stock_si_modulo_stock_no_esta_activo(): void
    {
        $stockModule = Modulo::where('slug', 'stock')->firstOrFail();
        $this->business->modulos()->updateExistingPivot($stockModule->id, [
            'activo' => false,
        ]);

        $this->postPurchase([
            $this->detail($this->product->id, 2, 100),
        ])->assertRedirect(route('gestion.compras.index', $this->business));

        $this->assertDatabaseCount('compras', 1);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_compra_rechaza_producto_de_otro_negocio(): void
    {
        $otherProduct = $this->otherBusiness->productos()->create([
            'nombre' => 'Producto ajeno',
            'codigo' => 'AJENO-COMPRA',
            'unidad' => 'unidad',
            'precio_venta' => 1000,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $this->postPurchase([
            $this->detail($otherProduct->id, 1, 100),
        ])->assertSessionHasErrors('detalles');

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('compra_detalles', 0);
    }

    public function test_compra_limita_detalles_a_cien(): void
    {
        $details = array_fill(0, 101, $this->detail($this->product->id, 1, 1));

        $this->postPurchase($details)
            ->assertSessionHasErrors('detalles');

        $this->assertDatabaseCount('compras', 0);
    }

    public function test_compra_revierte_todo_si_falla_entrada_stock(): void
    {
        StockMovimiento::creating(function (StockMovimiento $movement) {
            if ($movement->origen_tipo === 'compra') {
                throw new RuntimeException('Fallo simulado de stock en compra.');
            }
        });

        $this->withoutExceptionHandling();

        try {
            $this->postPurchase([
                $this->detail($this->product->id, 2, 100),
            ]);
            $this->fail('La excepción simulada no fue lanzada.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Fallo simulado de stock en compra.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('compra_detalles', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_compra_consulta_productos_una_sola_vez(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->postPurchase([
            $this->detail($this->product->id, 1, 100),
            $this->detail($this->product->id, 2, 100),
        ]);

        $productQueries = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(fn (string $query) => str_contains(
                strtolower($query),
                'from "productos"'
            ));

        DB::disableQueryLog();

        $this->assertCount(1, $productQueries);
    }

    public function test_reporte_rechaza_fecha_invalida_sin_error_500(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('gestion.reportes.index', $this->business))
            ->get(
            route('gestion.reportes.index', [
                'negocio' => $this->business,
                'desde' => 'fecha-invalida',
                'hasta' => '2026-01-31',
            ])
        );

        $response->assertRedirect(route('gestion.reportes.index', $this->business));
        $response->assertSessionHasErrors([
            'desde' => 'La fecha desde debe tener el formato AAAA-MM-DD.',
        ]);

        $this->get(route('gestion.reportes.index', $this->business))
            ->assertOk()
            ->assertSeeText('La fecha desde debe tener el formato AAAA-MM-DD.')
            ->assertSee('value="fecha-invalida"', false)
            ->assertSee('value="2026-01-31"', false);
    }

    public function test_reporte_muestra_error_para_fecha_hasta_invalida(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('gestion.reportes.index', $this->business))
            ->get(route('gestion.reportes.index', [
                'negocio' => $this->business,
                'desde' => '2026-01-01',
                'hasta' => 'fecha-invalida',
            ]));

        $response->assertSessionHasErrors([
            'hasta' => 'La fecha hasta debe tener el formato AAAA-MM-DD.',
        ]);

        $this->get(route('gestion.reportes.index', $this->business))
            ->assertOk()
            ->assertSeeText('La fecha hasta debe tener el formato AAAA-MM-DD.')
            ->assertSee('value="2026-01-01"', false)
            ->assertSee('value="fecha-invalida"', false);
    }

    public function test_pdf_rechaza_fecha_invalida_sin_generarlo(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('gestion.reportes.index', $this->business))
            ->get(
            route('gestion.reportes.pdf', [
                'negocio' => $this->business,
                'desde' => '2026-01-01',
                'hasta' => '31-01-2026',
            ])
        );

        $response->assertRedirect(route('gestion.reportes.index', $this->business));
        $response->assertSessionHasErrors([
            'hasta' => 'La fecha hasta debe tener el formato AAAA-MM-DD.',
        ]);
        $response->assertHeaderMissing('content-disposition');
    }

    public function test_pdf_se_genera_correctamente_con_periodo_valido(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('gestion.reportes.pdf', [
                'negocio' => $this->business,
                'desde' => '2026-01-01',
                'hasta' => '2026-01-31',
            ])
        );

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload(
                'reporte-negocio-compras-2026-01-01-2026-01-31.pdf'
            );

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_reporte_rechaza_hasta_anterior_a_desde(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('gestion.reportes.index', $this->business))
            ->get(
            route('gestion.reportes.index', [
                'negocio' => $this->business,
                'desde' => '2026-02-01',
                'hasta' => '2026-01-31',
            ])
        );

        $response->assertSessionHasErrors([
            'hasta' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
        ]);

        $this->get(route('gestion.reportes.index', $this->business))
            ->assertOk()
            ->assertSeeText('La fecha hasta debe ser igual o posterior a la fecha desde.')
            ->assertSee('value="2026-02-01"', false)
            ->assertSee('value="2026-01-31"', false);
    }

    public function test_reporte_rechaza_periodo_superior_a_366_dias(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('gestion.reportes.index', $this->business))
            ->get(
            route('gestion.reportes.index', [
                'negocio' => $this->business,
                'desde' => '2024-01-01',
                'hasta' => '2025-01-02',
            ])
        );

        $response->assertSessionHasErrors([
            'hasta' => 'El período del reporte no puede superar 366 días.',
        ]);

        $this->get(route('gestion.reportes.index', $this->business))
            ->assertOk()
            ->assertSeeText('El período del reporte no puede superar 366 días.')
            ->assertSee('value="2024-01-01"', false)
            ->assertSee('value="2025-01-02"', false);
    }

    public function test_reporte_acepta_periodo_valido(): void
    {
        $this->actingAs($this->admin)->get(
            route('gestion.reportes.index', [
                'negocio' => $this->business,
                'desde' => '2026-01-01',
                'hasta' => '2026-01-31',
            ])
        )->assertOk();
    }

    private function postPurchase(array $details)
    {
        return $this->actingAs($this->admin)->post(
            route('gestion.compras.store', $this->business),
            [
                'fecha' => now()->toDateString(),
                'proveedor' => 'Proveedor de prueba',
                'detalles' => $details,
            ]
        );
    }

    private function detail(int $productId, float $quantity, float $cost): array
    {
        return [
            'producto_id' => $productId,
            'cantidad' => $quantity,
            'costo_unitario' => $cost,
        ];
    }
}
