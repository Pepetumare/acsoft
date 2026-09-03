<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private Negocio $business;
    private User $admin;
    private Producto $product;

    protected function setUp(): void
    {
        parent::setUp();
        $client = Cliente::create(['nombre' => 'Cliente productos', 'activo' => true]);
        $this->business = Negocio::create(['cliente_id' => $client->id, 'nombre' => 'Negocio productos', 'slug' => 'productos-test', 'activo' => true]);

        foreach (['productos', 'stock', 'ventas', 'compras'] as $slug) {
            $module = Modulo::create(['nombre' => ucfirst($slug), 'slug' => $slug, 'ruta' => "gestion.{$slug}.index", 'activo' => true]);
            $this->business->modulos()->attach($module->id, ['activo' => true]);
        }

        $this->admin = User::factory()->create();
        $this->admin->negocios()->attach($this->business->id, ['rol' => 'admin', 'activo' => true]);
        $this->product = $this->business->productos()->create([
            'nombre' => 'Producto histórico', 'codigo' => 'HIST-1', 'unidad' => 'unidad',
            'precio_venta' => 1000, 'stock_minimo' => 1, 'activo' => true,
        ]);
    }

    public function test_desactivar_producto_conserva_ventas_compras_y_stock(): void
    {
        $sale = $this->business->ventas()->create(['user_id' => $this->admin->id, 'fecha' => now(), 'total' => 1000, 'metodo_pago' => 'Transferencia']);
        $detail = $sale->detalles()->create(['producto_id' => $this->product->id, 'descripcion' => $this->product->nombre, 'cantidad' => 1, 'precio_unitario' => 1000, 'subtotal' => 1000]);
        $purchase = $this->business->compras()->create(['user_id' => $this->admin->id, 'fecha' => now(), 'total' => 500]);
        $purchaseDetail = $purchase->detalles()->create(['producto_id' => $this->product->id, 'cantidad' => 1, 'costo_unitario' => 500, 'subtotal' => 500]);
        $movement = $this->business->movimientosStock()->create(['producto_id' => $this->product->id, 'tipo' => 'entrada', 'cantidad' => 1, 'concepto' => 'Histórico']);

        $this->actingAs($this->admin)->delete(route('gestion.productos.destroy', [$this->business, $this->product]))->assertRedirect();

        $this->assertDatabaseHas('productos', ['id' => $this->product->id, 'activo' => false]);
        $this->assertDatabaseHas('venta_detalles', ['id' => $detail->id, 'producto_id' => $this->product->id]);
        $this->assertDatabaseHas('compra_detalles', ['id' => $purchaseDetail->id, 'producto_id' => $this->product->id]);
        $this->assertDatabaseHas('stock_movimientos', ['id' => $movement->id, 'producto_id' => $this->product->id]);
    }

    public function test_producto_inactivo_no_puede_usarse_en_nuevas_operaciones(): void
    {
        $this->product->update(['activo' => false]);

        $this->actingAs($this->admin)->post(route('gestion.ventas.store', $this->business), [
            'fecha' => now()->toDateString(), 'metodo_pago' => 'Transferencia', 'operation_token' => (string) Str::uuid(),
            'detalles' => [['producto_id' => $this->product->id, 'descripcion' => $this->product->nombre, 'cantidad' => 1, 'precio_unitario' => 1000]],
        ])->assertSessionHasErrors('detalles');

        $this->actingAs($this->admin)->post(route('gestion.compras.store', $this->business), [
            'fecha' => now()->toDateString(), 'operation_token' => (string) Str::uuid(),
            'detalles' => [['producto_id' => $this->product->id, 'cantidad' => 1, 'costo_unitario' => 500]],
        ])->assertSessionHasErrors('detalles');

        $this->actingAs($this->admin)->post(route('gestion.stock.store', $this->business), [
            'producto_id' => $this->product->id, 'tipo' => 'entrada', 'cantidad' => 1, 'concepto' => 'No permitido',
        ])->assertNotFound();

        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('compras', 0);
        $this->assertDatabaseCount('stock_movimientos', 0);
    }

    public function test_producto_puede_reactivarse_desde_edicion(): void
    {
        $this->product->update(['activo' => false]);

        $this->actingAs($this->admin)->put(route('gestion.productos.update', [$this->business, $this->product]), [
            'nombre' => $this->product->nombre, 'codigo' => $this->product->codigo,
            'unidad' => $this->product->unidad, 'precio_venta' => 1000, 'stock_minimo' => 1, 'activo' => 1,
        ])->assertRedirect();

        $this->assertTrue($this->product->fresh()->activo);
    }
}
