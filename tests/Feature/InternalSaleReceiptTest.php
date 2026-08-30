<?php

namespace Tests\Feature;

use App\Models\CajaMovimiento;
use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InternalSaleReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Negocio $business;
    private Negocio $otherBusiness;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Cliente::create([
            'nombre' => 'Cliente comprobantes',
            'activo' => true,
        ]);

        $this->business = $this->createBusiness($client->id, 'Negocio Uno', 'negocio-uno');
        $this->otherBusiness = $this->createBusiness($client->id, 'Negocio Dos', 'negocio-dos');

        $modules = [];
        foreach (['ventas', 'productos', 'stock', 'caja'] as $slug) {
            $modules[$slug] = Modulo::create([
                'nombre' => ucfirst($slug),
                'slug' => $slug,
                'activo' => true,
            ]);
        }

        foreach ([$this->business, $this->otherBusiness] as $business) {
            foreach ($modules as $module) {
                $business->modulos()->attach($module->id, ['activo' => true]);
            }
        }

        $this->user = $this->createBusinessUser($this->business);
        $this->otherUser = $this->createBusinessUser($this->otherBusiness);
    }

    public function test_venta_genera_numero_interno_consecutivo_por_negocio(): void
    {
        $this->postManualSale($this->user, $this->business, 'Primera venta');
        $this->postManualSale($this->user, $this->business, 'Segunda venta');

        $this->assertSame(
            [1, 2],
            $this->business->ventas()
                ->orderBy('id')
                ->pluck('numero_documento_interno')
                ->all()
        );
    }

    public function test_negocios_tienen_secuencias_independientes(): void
    {
        $this->postManualSale($this->user, $this->business, 'Venta negocio uno');
        $this->postManualSale($this->otherUser, $this->otherBusiness, 'Venta negocio dos');

        $this->assertSame(1, $this->business->ventas()->firstOrFail()->numero_documento_interno);
        $this->assertSame(1, $this->otherBusiness->ventas()->firstOrFail()->numero_documento_interno);
    }

    public function test_usuario_accede_a_boleta_de_su_negocio_y_otro_tenant_recibe_403(): void
    {
        $this->postManualSale($this->user, $this->business, 'Producto propio');
        $sale = $this->business->ventas()->firstOrFail();

        $this->actingAs($this->user)
            ->get(route('gestion.ventas.receipt', [$this->business, $sale]))
            ->assertOk();

        $this->actingAs($this->otherUser)
            ->get(route('gestion.ventas.receipt', [$this->business, $sale]))
            ->assertForbidden();

        $this->postManualSale($this->otherUser, $this->otherBusiness, 'Venta ajena');
        $otherSale = $this->otherBusiness->ventas()->firstOrFail();

        $this->actingAs($this->user)
            ->get(route('gestion.ventas.receipt', [$this->business, $otherSale]))
            ->assertForbidden();
    }

    public function test_boleta_muestra_detalle_total_pago_y_advertencia(): void
    {
        $this->postManualSale($this->user, $this->business, 'Café molido', 2, 3500, 'Transferencia');
        $sale = $this->business->ventas()->firstOrFail();

        $this->actingAs($this->user)
            ->get(route('gestion.ventas.receipt', [$this->business, $sale]))
            ->assertOk()
            ->assertSeeText('Boleta interna')
            ->assertSeeText('N.º 000001')
            ->assertSeeText('Café molido')
            ->assertSeeText('2,000')
            ->assertSeeText('$3.500')
            ->assertSeeText('$7.000')
            ->assertSeeText('Transferencia')
            ->assertSeeText('Documento interno. No válido como documento tributario ante el SII.')
            ->assertSeeText('Imprimir');
    }

    public function test_reenvio_idempotente_descuenta_stock_y_crea_caja_una_sola_vez(): void
    {
        $product = $this->business->productos()->create([
            'nombre' => 'Producto controlado',
            'codigo' => 'CONTROL-1',
            'unidad' => 'unidad',
            'precio_venta' => 1000,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $this->business->movimientosStock()->create([
            'producto_id' => $product->id,
            'user_id' => $this->user->id,
            'tipo' => 'entrada',
            'cantidad' => 10,
            'concepto' => 'Stock inicial',
        ]);

        $this->business->cajas()->create([
            'user_apertura_id' => $this->user->id,
            'fecha' => now()->toDateString(),
            'saldo_inicial' => 10000,
            'estado' => 'abierta',
            'abierta_en' => now(),
        ]);

        $token = (string) Str::uuid();
        $data = $this->saleData('Producto controlado', 2, 1000, 'Efectivo', $token, $product->id);

        $this->postSale($this->user, $this->business, $data);
        $this->postSale($this->user, $this->business, $data);

        $sale = $this->business->ventas()->firstOrFail();
        $this->assertDatabaseCount('ventas', 1);
        $this->assertSame(8.0, $product->stockActual());
        $this->assertSame(1, $this->business->movimientosStock()
            ->where('origen_tipo', 'venta')
            ->where('origen_id', $sale->id)
            ->count());
        $this->assertSame(1, CajaMovimiento::query()
            ->where('origen_tipo', 'venta')
            ->where('origen_id', $sale->id)
            ->count());
    }

    public function test_venta_antigua_sin_numero_sigue_mostrando_comprobante_compatible(): void
    {
        $sale = $this->business->ventas()->create([
            'user_id' => $this->user->id,
            'fecha' => now()->toDateString(),
            'total' => 1200,
            'metodo_pago' => 'Efectivo',
        ]);
        $sale->detalles()->create([
            'descripcion' => 'Registro histórico',
            'cantidad' => 1,
            'precio_unitario' => 1200,
            'subtotal' => 1200,
        ]);

        $this->actingAs($this->user)
            ->get(route('gestion.ventas.receipt', [$this->business, $sale]))
            ->assertOk()
            ->assertSeeText('LEG-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT))
            ->assertSeeText('Registro histórico');

        $this->assertNull($sale->fresh()->numero_documento_interno);
    }

    private function createBusiness(int $clientId, string $name, string $slug): Negocio
    {
        return Negocio::create([
            'cliente_id' => $clientId,
            'nombre' => $name,
            'slug' => $slug,
            'activo' => true,
        ]);
    }

    private function createBusinessUser(Negocio $business): User
    {
        $user = User::factory()->create(['is_superadmin' => false]);
        $user->negocios()->attach($business->id, [
            'rol' => 'usuario',
            'activo' => true,
        ]);

        return $user;
    }

    private function postManualSale(
        User $user,
        Negocio $business,
        string $description,
        float $quantity = 1,
        float $price = 1000,
        string $paymentMethod = 'Transferencia'
    ) {
        return $this->postSale(
            $user,
            $business,
            $this->saleData($description, $quantity, $price, $paymentMethod)
        );
    }

    private function postSale(User $user, Negocio $business, array $data)
    {
        return $this->actingAs($user)->post(
            route('gestion.ventas.store', $business),
            $data
        );
    }

    private function saleData(
        string $description,
        float $quantity,
        float $price,
        string $paymentMethod,
        ?string $token = null,
        ?int $productId = null
    ): array {
        return [
            'fecha' => now()->toDateString(),
            'metodo_pago' => $paymentMethod,
            'operation_token' => $token ?? (string) Str::uuid(),
            'detalles' => [[
                'producto_id' => $productId,
                'descripcion' => $description,
                'cantidad' => $quantity,
                'precio_unitario' => $price,
            ]],
        ];
    }
}
