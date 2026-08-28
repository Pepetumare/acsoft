<?php

namespace Tests\Feature;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CashRegisterExpenseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Negocio $business;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Cliente::create([
            'nombre' => 'Cliente caja',
            'activo' => true,
        ]);

        $this->business = Negocio::create([
            'cliente_id' => $client->id,
            'nombre' => 'Negocio caja',
            'slug' => 'negocio-caja',
            'activo' => true,
        ]);

        foreach (['caja', 'gastos', 'ventas'] as $slug) {
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
    }

    public function test_puede_abrir_una_caja(): void
    {
        $this->openCashRegister(10000)
            ->assertRedirect(route('gestion.caja.index', $this->business));

        $this->assertDatabaseHas('cajas', [
            'negocio_id' => $this->business->id,
            'estado' => 'abierta',
            'saldo_inicial' => 10000,
        ]);
    }

    public function test_impide_abrir_dos_cajas_para_el_mismo_negocio(): void
    {
        $this->openCashRegister(10000);

        $this->openCashRegister(20000)
            ->assertSessionHas('error', 'Ya existe una caja abierta.');

        $this->assertSame(
            1,
            $this->business->cajas()->where('estado', 'abierta')->count()
        );
    }

    public function test_cierra_caja_con_saldo_esperado_y_diferencia(): void
    {
        $cashRegister = $this->createOpenCashRegister(10000);
        $this->addMovement($cashRegister, 'ingreso', 5000);
        $this->addMovement($cashRegister, 'egreso', 2000);

        $this->actingAs($this->user)->post(
            route('gestion.caja.destroy', $this->business),
            ['saldo_contado' => 12500]
        )->assertRedirect(route('gestion.caja.index', $this->business));

        $this->assertDatabaseHas('cajas', [
            'id' => $cashRegister->id,
            'estado' => 'cerrada',
            'saldo_esperado' => 13000,
            'saldo_contado' => 12500,
            'diferencia' => -500,
        ]);
    }

    public function test_caja_cerrada_no_puede_recibir_movimientos(): void
    {
        $cashRegister = $this->createClosedCashRegister();

        $this->actingAs($this->user)->post(
            route('gestion.caja.movimientos.store', $this->business),
            [
                'tipo' => 'ingreso',
                'concepto' => 'Movimiento tardío',
                'monto' => 1000,
            ]
        )->assertSessionHas(
            'error',
            'La caja está cerrada y no puede recibir movimientos.'
        );

        $this->assertDatabaseMissing('caja_movimientos', [
            'caja_id' => $cashRegister->id,
            'concepto' => 'Movimiento tardío',
        ]);
    }

    public function test_totales_de_caja_se_calculan_con_una_sola_consulta(): void
    {
        $cashRegister = $this->createOpenCashRegister(10000);
        $this->addMovement($cashRegister, 'ingreso', 5000);
        $this->addMovement($cashRegister, 'egreso', 2000);
        $cashRegister = $cashRegister->fresh();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->assertSame(5000.0, $cashRegister->totalIngresos());
        $this->assertSame(2000.0, $cashRegister->totalEgresos());
        $this->assertSame(13000.0, $cashRegister->calcularSaldoEsperado());

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(1, $queries);
    }
    public function test_gasto_en_efectivo_con_caja_abierta_crea_movimiento(): void
    {
        $cashRegister = $this->createOpenCashRegister();

        $this->postExpense()->assertRedirect(
            route('gestion.gastos.index', $this->business)
        );

        $expense = Gasto::firstOrFail();
        $this->assertDatabaseHas('caja_movimientos', [
            'caja_id' => $cashRegister->id,
            'tipo' => 'egreso',
            'monto' => 2500,
            'origen_tipo' => 'gasto',
            'origen_id' => $expense->id,
        ]);
    }

    public function test_gasto_en_efectivo_sin_caja_es_rechazado_si_modulo_caja_esta_activo(): void
    {
        $this->postExpense()->assertSessionHas(
            'error',
            'Debe existir una caja abierta para registrar un gasto en efectivo.'
        );

        $this->assertDatabaseCount('gastos', 0);
        $this->assertDatabaseCount('caja_movimientos', 0);
    }

    public function test_gasto_en_efectivo_sin_modulo_caja_se_registra_normalmente(): void
    {
        $cashModule = Modulo::where('slug', 'caja')->firstOrFail();
        $this->business->modulos()->updateExistingPivot($cashModule->id, [
            'activo' => false,
        ]);

        $this->postExpense()->assertRedirect(
            route('gestion.gastos.index', $this->business)
        );

        $this->assertDatabaseCount('gastos', 1);
        $this->assertDatabaseCount('caja_movimientos', 0);
    }

    public function test_gasto_se_revierte_si_falla_movimiento_automatico(): void
    {
        $this->createOpenCashRegister();

        CajaMovimiento::creating(function (CajaMovimiento $movement) {
            if ($movement->origen_tipo === 'gasto') {
                throw new RuntimeException('Fallo simulado de caja.');
            }
        });

        $this->withoutExceptionHandling();

        try {
            $this->postExpense();
            $this->fail('La excepción simulada no fue lanzada.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo simulado de caja.', $exception->getMessage());
        }

        $this->assertDatabaseCount('gastos', 0);
        $this->assertDatabaseCount('caja_movimientos', 0);
    }

    public function test_no_permite_eliminar_venta_ligada_a_caja_cerrada(): void
    {
        $cashRegister = $this->createClosedCashRegister();
        $sale = $this->createSaleWithCashMovement($cashRegister);

        $this->actingAs($this->user)->delete(
            route('gestion.ventas.destroy', [$this->business, $sale])
        )->assertSessionHas(
            'error',
            'No se puede eliminar la venta porque pertenece a una caja cerrada.'
        );

        $this->assertDatabaseHas('ventas', ['id' => $sale->id]);
        $this->assertDatabaseHas('caja_movimientos', [
            'origen_tipo' => 'venta',
            'origen_id' => $sale->id,
        ]);
    }

    public function test_no_permite_eliminar_gasto_ligado_a_caja_cerrada(): void
    {
        $cashRegister = $this->createClosedCashRegister();
        $expense = $this->createExpenseWithCashMovement($cashRegister);

        $this->actingAs($this->user)->delete(
            route('gestion.gastos.destroy', [$this->business, $expense])
        )->assertSessionHas(
            'error',
            'No se puede eliminar el gasto porque pertenece a una caja cerrada.'
        );

        $this->assertDatabaseHas('gastos', ['id' => $expense->id]);
        $this->assertDatabaseHas('caja_movimientos', [
            'origen_tipo' => 'gasto',
            'origen_id' => $expense->id,
        ]);
    }

    public function test_permite_eliminar_operaciones_si_caja_sigue_abierta(): void
    {
        $cashRegister = $this->createOpenCashRegister();
        $sale = $this->createSaleWithCashMovement($cashRegister);
        $expense = $this->createExpenseWithCashMovement($cashRegister);

        $this->actingAs($this->user)->delete(
            route('gestion.ventas.destroy', [$this->business, $sale])
        )->assertRedirect(route('gestion.ventas.index', $this->business));

        $this->actingAs($this->user)->delete(
            route('gestion.gastos.destroy', [$this->business, $expense])
        )->assertRedirect(route('gestion.gastos.index', $this->business));

        $this->assertDatabaseMissing('ventas', ['id' => $sale->id]);
        $this->assertDatabaseMissing('gastos', ['id' => $expense->id]);
        $this->assertDatabaseMissing('caja_movimientos', [
            'origen_tipo' => 'venta',
            'origen_id' => $sale->id,
        ]);
        $this->assertDatabaseMissing('caja_movimientos', [
            'origen_tipo' => 'gasto',
            'origen_id' => $expense->id,
        ]);
    }

    private function openCashRegister(float $initialBalance)
    {
        return $this->actingAs($this->user)->post(
            route('gestion.caja.store', $this->business),
            ['saldo_inicial' => $initialBalance]
        );
    }

    private function createOpenCashRegister(float $initialBalance = 10000): Caja
    {
        return $this->business->cajas()->create([
            'user_apertura_id' => $this->user->id,
            'fecha' => now()->toDateString(),
            'saldo_inicial' => $initialBalance,
            'estado' => 'abierta',
            'abierta_en' => now(),
        ]);
    }

    private function createClosedCashRegister(): Caja
    {
        return $this->business->cajas()->create([
            'user_apertura_id' => $this->user->id,
            'user_cierre_id' => $this->user->id,
            'fecha' => now()->toDateString(),
            'saldo_inicial' => 10000,
            'saldo_esperado' => 10000,
            'saldo_contado' => 10000,
            'diferencia' => 0,
            'estado' => 'cerrada',
            'abierta_en' => now()->subHour(),
            'cerrada_en' => now(),
        ]);
    }

    private function addMovement(Caja $cashRegister, string $type, float $amount): void
    {
        $cashRegister->movimientos()->create([
            'user_id' => $this->user->id,
            'tipo' => $type,
            'concepto' => ucfirst($type),
            'monto' => $amount,
        ]);
    }

    private function postExpense()
    {
        return $this->actingAs($this->user)->post(
            route('gestion.gastos.store', $this->business),
            [
                'fecha' => now()->toDateString(),
                'concepto' => 'Gasto en efectivo',
                'monto' => 2500,
                'metodo_pago' => 'Efectivo',
            ]
        );
    }

    private function createSaleWithCashMovement(Caja $cashRegister): Venta
    {
        $sale = $this->business->ventas()->create([
            'user_id' => $this->user->id,
            'fecha' => now()->toDateString(),
            'total' => 5000,
            'metodo_pago' => 'Efectivo',
        ]);

        $cashRegister->movimientos()->create([
            'user_id' => $this->user->id,
            'tipo' => 'ingreso',
            'concepto' => 'Venta #'.$sale->id,
            'monto' => $sale->total,
            'origen_tipo' => 'venta',
            'origen_id' => $sale->id,
        ]);

        return $sale;
    }

    private function createExpenseWithCashMovement(Caja $cashRegister): Gasto
    {
        $expense = $this->business->gastos()->create([
            'user_id' => $this->user->id,
            'fecha' => now()->toDateString(),
            'concepto' => 'Gasto ligado',
            'monto' => 2500,
            'metodo_pago' => 'Efectivo',
        ]);

        $cashRegister->movimientos()->create([
            'user_id' => $this->user->id,
            'tipo' => 'egreso',
            'concepto' => 'Gasto #'.$expense->id,
            'monto' => $expense->monto,
            'origen_tipo' => 'gasto',
            'origen_id' => $expense->id,
        ]);

        return $expense;
    }
}
