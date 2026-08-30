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

class SaleStockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Negocio $business;

    private Negocio $otherBusiness;

    private Producto $product;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Cliente::create([
            'nombre' => 'Cliente inventario',
            'activo' => true,
        ]);

        $this->business = Negocio::create([
            'cliente_id' => $client->id,
            'nombre' => 'Negocio inventario',
            'slug' => 'negocio-inventario',
            'activo' => true,
        ]);

        $this->otherBusiness = Negocio::create([
            'cliente_id' => $client->id,
            'nombre' => 'Otro negocio',
            'slug' => 'otro-negocio-inventario',
            'activo' => true,
        ]);

        foreach (['ventas', 'productos', 'stock', 'caja'] as $slug) {
            $module = Modulo::create([
                'nombre' => ucfirst($slug),
                'slug' => $slug,
                'activo' => true,
            ]);

            $this->business->modulos()->attach($module->id, ['activo' => true]);
        }

        $this->user = User::factory()->create(['is_superadmin' => false]);
        $this->user->negocios()->attach($this->business->id, [
            'rol' => 'usuario',
            'activo' => true,
        ]);

        $this->product = $this->business->productos()->create([
            'nombre' => 'Producto A',
            'codigo' => 'PROD-A',
            'unidad' => 'unidad',
            'precio_venta' => 1000,
            'stock_minimo' => 0,
            'activo' => true,
        ]);
    }

    public function test_venta_manual_sin_producto_sigue_funcionando(): void
    {
        $this->postSale([
            $this->detail(null, 2, 1500, 'Servicio manual'),
        ])->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->assertDatabaseHas('ventas', [
            'negocio_id' => $this->business->id,
            'total' => 3000,
        ]);
        $this->assertDatabaseHas('venta_detalles', [
            'producto_id' => null,
            'descripcion' => 'Servicio manual',
        ]);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_venta_con_producto_sin_modulo_stock_no_crea_salida(): void
    {
        $stockModule = Modulo::where('slug', 'stock')->firstOrFail();
        $this->business->modulos()->updateExistingPivot($stockModule->id, [
            'activo' => false,
        ]);

        $this->postSale([
            $this->detail($this->product->id, 2, 1000),
        ])->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->assertDatabaseHas('venta_detalles', [
            'producto_id' => $this->product->id,
            'cantidad' => 2,
        ]);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_venta_con_stock_suficiente_crea_salida_automatica(): void
    {
        $this->addStock(10);

        $this->postSale([
            $this->detail($this->product->id, 4, 1000),
        ])->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->assertSame(6.0, $this->product->stockActual());
        $this->assertDatabaseHas('stock_movimientos', [
            'producto_id' => $this->product->id,
            'tipo' => 'salida',
            'cantidad' => 4,
            'origen_tipo' => 'venta',
        ]);
    }

    public function test_venta_de_quince_unidades_es_permitida(): void
    {
        $this->addStock(20);

        $this->postSale([
            $this->detail($this->product->id, 15, 1000),
        ])->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->assertDatabaseHas('venta_detalles', [
            'producto_id' => $this->product->id,
            'cantidad' => 15,
        ]);
        $this->assertSame(5.0, $this->product->stockActual());
    }

    public function test_venta_de_catorce_coma_999_unidades_es_rechazada_sin_modificar_stock(): void
    {
        $this->addStock(20);

        $this->postSale([
            $this->detail($this->product->id, 14.999, 1000),
        ])->assertSessionHasErrors([
            'detalles.0.cantidad' => 'La cantidad para productos medidos en unidad debe ser un número entero mayor o igual a 1.',
        ]);

        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_detalles', 0);
        $this->assertSame(20.0, $this->product->stockActual());
        $this->assertDatabaseMissing('stock_movimientos', [
            'producto_id' => $this->product->id,
            'tipo' => 'salida',
        ]);
    }

    public function test_venta_de_dos_coma_cinco_kilos_es_permitida(): void
    {
        $product = $this->createFractionalProduct();
        $this->addStock(10, $product);

        $this->postSale([
            $this->detail($product->id, 2.5, 1000, $product->nombre),
        ])->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->assertDatabaseHas('venta_detalles', [
            'producto_id' => $product->id,
            'cantidad' => 2.5,
        ]);
    }

    public function test_venta_de_dos_coma_125_kilos_es_permitida(): void
    {
        $product = $this->createFractionalProduct();
        $this->addStock(10, $product);

        $this->postSale([
            $this->detail($product->id, 2.125, 1000, $product->nombre),
        ])->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->assertDatabaseHas('venta_detalles', [
            'producto_id' => $product->id,
            'cantidad' => 2.125,
        ]);
    }

    public function test_venta_en_efectivo_con_modulo_caja_y_caja_abierta_es_permitida(): void
    {
        $cashRegister = $this->business->cajas()->create([
            'user_apertura_id' => $this->user->id,
            'fecha' => now()->toDateString(),
            'saldo_inicial' => 10000,
            'estado' => 'abierta',
            'abierta_en' => now(),
        ]);
        $this->addStock(10);

        $this->postSale([
            $this->detail($this->product->id, 2, 1000),
        ], 'Efectivo')->assertRedirect(
            route('gestion.ventas.index', $this->business)
        );

        $sale = $this->business->ventas()->firstOrFail();
        $this->assertDatabaseHas('venta_detalles', [
            'venta_id' => $sale->id,
            'producto_id' => $this->product->id,
        ]);
        $this->assertSame(8.0, $this->product->stockActual());
        $this->assertDatabaseHas('caja_movimientos', [
            'caja_id' => $cashRegister->id,
            'tipo' => 'ingreso',
            'monto' => 2000,
            'origen_tipo' => 'venta',
            'origen_id' => $sale->id,
        ]);
    }

    public function test_venta_en_efectivo_con_modulo_caja_sin_caja_abierta_es_rechazada(): void
    {
        $this->addStock(10);

        $response = $this->postSale([
            $this->detail($this->product->id, 2, 1000),
        ], 'Efectivo');

        $response->assertSessionHasErrors([
            'metodo_pago' => 'Debe existir una caja abierta para registrar una venta en efectivo.',
        ]);
        $response->assertSessionHasInput('metodo_pago', 'Efectivo');
        $response->assertSessionHasInput(
            'detalles.0.descripcion',
            'Producto A'
        );

        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_detalles', 0);
        $this->assertDatabaseCount('caja_movimientos', 0);
    }

    public function test_venta_en_efectivo_rechazada_no_modifica_stock(): void
    {
        $this->addStock(10);

        $this->postSale([
            $this->detail($this->product->id, 2, 1000),
        ], 'Efectivo')->assertSessionHasErrors('metodo_pago');

        $this->assertSame(10.0, $this->product->stockActual());
        $this->assertDatabaseMissing('stock_movimientos', [
            'producto_id' => $this->product->id,
            'tipo' => 'salida',
        ]);
    }

    public function test_venta_en_efectivo_sin_modulo_caja_es_permitida(): void
    {
        $cashModule = Modulo::where('slug', 'caja')->firstOrFail();
        $this->business->modulos()->updateExistingPivot($cashModule->id, [
            'activo' => false,
        ]);
        $this->addStock(10);

        $this->postSale([
            $this->detail($this->product->id, 2, 1000),
        ], 'Efectivo')->assertRedirect(
            route('gestion.ventas.index', $this->business)
        );

        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('venta_detalles', 1);
        $this->assertSame(8.0, $this->product->stockActual());
        $this->assertDatabaseCount('caja_movimientos', 0);
    }

    public function test_venta_no_efectiva_sin_caja_abierta_es_permitida(): void
    {
        $this->addStock(10);

        $this->postSale([
            $this->detail($this->product->id, 2, 1000),
        ], 'Transferencia')->assertRedirect(
            route('gestion.ventas.index', $this->business)
        );

        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('venta_detalles', 1);
        $this->assertSame(8.0, $this->product->stockActual());
        $this->assertDatabaseCount('caja_movimientos', 0);
    }

    public function test_venta_con_stock_insuficiente_es_rechazada_sin_dejar_stock_negativo(): void
    {
        $this->addStock(5);

        $this->postSale([
            $this->detail($this->product->id, 6, 1000),
        ])->assertSessionHasErrors('detalles');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(5.0, $this->product->stockActual());
        $this->assertGreaterThanOrEqual(0, $this->product->stockActual());
    }

    public function test_producto_repetido_se_valida_por_cantidad_total(): void
    {
        $this->addStock(10);

        $this->postSale([
            $this->detail($this->product->id, 4, 1000, 'Primera línea'),
            $this->detail($this->product->id, 5, 1000, 'Segunda línea'),
        ])->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->assertDatabaseCount('venta_detalles', 2);
        $this->assertSame(1.0, $this->product->stockActual());
        $this->assertGreaterThanOrEqual(0, $this->product->stockActual());
    }

    public function test_producto_de_otro_negocio_es_rechazado(): void
    {
        $otherProduct = $this->otherBusiness->productos()->create([
            'nombre' => 'Producto ajeno',
            'codigo' => 'AJENO',
            'unidad' => 'unidad',
            'precio_venta' => 1000,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $this->postSale([
            $this->detail($otherProduct->id, 1, 1000),
        ])->assertSessionHasErrors('detalles.0.producto_id');

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_venta_limita_el_numero_de_detalles_a_cien(): void
    {
        $details = array_fill(0, 101, $this->detail(null, 1, 1));

        $this->postSale($details)
            ->assertSessionHasErrors('detalles');

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_transaccion_revierte_venta_detalles_y_stock_si_falla_una_parte(): void
    {
        $this->addStock(10);

        StockMovimiento::creating(function (StockMovimiento $movement) {
            if ($movement->tipo === 'salida') {
                throw new RuntimeException('Fallo simulado al crear salida de stock.');
            }
        });

        $this->withoutExceptionHandling();

        try {
            $this->postSale([
                $this->detail($this->product->id, 2, 1000),
            ]);

            $this->fail('La excepción simulada no fue lanzada.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Fallo simulado al crear salida de stock.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_detalles', 0);
        $this->assertSame(10.0, $this->product->stockActual());
    }

    public function test_listado_de_productos_calcula_stock_en_una_sola_consulta(): void
    {
        $this->addStock(10);

        for ($number = 1; $number <= 5; $number++) {
            $this->business->productos()->create([
                'nombre' => 'Producto '.$number,
                'codigo' => 'PROD-'.$number,
                'unidad' => 'unidad',
                'precio_venta' => 100,
                'stock_minimo' => 0,
                'activo' => true,
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $products = $this->business
            ->productos()
            ->withStockActual()
            ->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(6, $products);
        $this->assertCount(1, $queries);
        $this->assertSame(10.0, (float) $products->firstWhere('id', $this->product->id)->stock_actual);
    }

    private function postSale(
        array $details,
        string $paymentMethod = 'Transferencia'
    ) {
        return $this->actingAs($this->user)->post(
            route('gestion.ventas.store', $this->business),
            [
                'fecha' => now()->toDateString(),
                'metodo_pago' => $paymentMethod,
                'detalles' => $details,
            ]
        );
    }

    private function detail(
        ?int $productId,
        float $quantity,
        float $price,
        string $description = 'Producto A'
    ): array {
        return [
            'producto_id' => $productId,
            'descripcion' => $description,
            'cantidad' => $quantity,
            'precio_unitario' => $price,
        ];
    }

    private function addStock(
        float $quantity,
        ?Producto $product = null
    ): void
    {
        $this->business->movimientosStock()->create([
            'producto_id' => ($product ?? $this->product)->id,
            'user_id' => $this->user->id,
            'tipo' => 'entrada',
            'cantidad' => $quantity,
            'concepto' => 'Stock inicial',
        ]);
    }

    private function createFractionalProduct(): Producto
    {
        return $this->business->productos()->create([
            'nombre' => 'Producto por peso',
            'codigo' => 'PROD-KG',
            'unidad' => 'kg',
            'precio_venta' => 1000,
            'stock_minimo' => 0,
            'activo' => true,
        ]);
    }
}
