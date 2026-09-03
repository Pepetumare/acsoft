<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\Venta;
use App\Models\VentaContador;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class NegocioPruebaSeeder extends Seeder
{
    private const MARKER = '[ACSOFT-DEMO]';

    public function run(): void
    {
        if (app()->environment('production') || config('database.connections.'.config('database.default').'.database') !== 'acsoft') {
            throw new RuntimeException('SAFETY: NegocioPruebaSeeder solo puede ejecutarse en la base local acsoft y nunca en producción.');
        }

        $negocio = Negocio::query()->where('slug', 'negocio-prueba')->first();

        if (! $negocio) {
            throw new RuntimeException('No existe el negocio local con slug negocio-prueba. No se creó ningún dato.');
        }

        $usuarios = $negocio->usuarios()->wherePivot('activo', true)->get();
        $admin = $usuarios->first(fn ($user) => $user->pivot->rol === 'admin');
        $vendedor = $usuarios->first(fn ($user) => $user->pivot->rol === 'usuario');

        if (! $admin || ! $vendedor) {
            throw new RuntimeException('NegocioPruebaSeeder requiere un admin y un usuario operativo activos asociados a negocio-prueba.');
        }

        DB::transaction(function () use ($negocio, $admin, $vendedor): void {
            $hoy = CarbonImmutable::today();
            $productos = $this->crearProductos($negocio);
            $planVentas = $this->planVentas($hoy, $productos);
            $vendidoPorProducto = collect($planVentas)->flatMap(fn (array $venta) => $venta['detalles'])
                ->groupBy('producto_id')->map(fn ($detalles) => (float) $detalles->sum('cantidad'));

            $this->crearCompras($negocio, $admin->id, $hoy, $productos, $vendidoPorProducto);
            $cajas = $this->crearCajas($negocio, $admin->id, $hoy);
            $this->crearVentas($negocio, $admin->id, $vendedor->id, $planVentas, $cajas);
            $this->crearGastos($negocio, $admin->id, $vendedor->id, $hoy, $cajas);
            $this->cerrarCajas($cajas, $admin->id);
        }, 3);
    }

    /** @return \Illuminate\Support\Collection<int, Producto> */
    private function crearProductos(Negocio $negocio)
    {
        $data = [
            ['Bebida Cola 1.5 L', 'DEMO-COLA15', 'unidad', 2490, 12],
            ['Agua Mineral 1.5 L', 'DEMO-AGUA15', 'unidad', 1290, 8],
            ['Leche Entera 1 L', 'DEMO-LECHE1', 'unidad', 1390, 10],
            ['Pan Molde', 'DEMO-PANMOLDE', 'unidad', 2290, 8],
            ['Arroz 1 kg', 'DEMO-ARROZ1', 'unidad', 1890, 8],
            ['Fideos 400 g', 'DEMO-FIDEOS400', 'unidad', 1090, 10],
            ['Aceite 1 L', 'DEMO-ACEITE1', 'unidad', 3290, 6],
            ['Azúcar 1 kg', 'DEMO-AZUCAR1', 'unidad', 1490, 8],
            ['Harina 1 kg', 'DEMO-HARINA1', 'unidad', 1290, 8],
            ['Detergente 750 ml', 'DEMO-DETERGENTE', 'unidad', 2990, 4],
            ['Papel Higiénico 4 rollos', 'DEMO-PAPEL4', 'paquete', 3490, 5],
            ['Atún en lata', 'DEMO-ATUN', 'unidad', 1790, 5],
            ['Café 170 g', 'DEMO-CAFE170', 'unidad', 5490, 6],
            ['Galletas surtidas', 'DEMO-GALLETAS', 'paquete', 1690, 8],
            ['Jugo de naranja 1 L', 'DEMO-JUGO1', 'unidad', 1990, 7],
            ['Huevos 12 unidades', 'DEMO-HUEVOS12', 'caja', 3990, 5],
            ['Sal 1 kg', 'DEMO-SAL1', 'unidad', 990, 6],
            ['Lavalozas 750 ml', 'DEMO-LAVALOZAS', 'unidad', 2190, 5],
            ['Chocolate en barra', 'DEMO-CHOCOLATE', 'unidad', 1490, 6],
            ['Cereal 400 g', 'DEMO-CEREAL400', 'unidad', 3790, 5],
        ];

        return collect($data)->map(function (array $item) use ($negocio) {
            return Producto::query()->updateOrCreate(
                ['negocio_id' => $negocio->id, 'codigo' => $item[1]],
                ['nombre' => $item[0], 'unidad' => $item[2], 'precio_venta' => $item[3], 'stock_minimo' => $item[4], 'activo' => true]
            );
        })->values();
    }

    private function planVentas(CarbonImmutable $hoy, $productos): array
    {
        $plan = [];
        $ponderados = [0, 0, 0, 3, 3, 1, 12, 13, 4, 5, 6, 7, 8, 9, 10, 11, 14, 15, 16, 17, 18, 19];
        $indice = 0;

        for ($offset = 74; $offset >= 0; $offset--) {
            $fecha = $hoy->subDays($offset);
            $cantidadDia = match ($fecha->dayOfWeekIso) { 5 => 3, 6 => 4, default => 2 };

            for ($turno = 0; $turno < $cantidadDia; $turno++, $indice++) {
                $indices = [$ponderados[$indice % count($ponderados)]];
                if ($indice % 2 === 0) { $indices[] = ($indice * 7 + 4) % $productos->count(); }
                if ($indice % 5 === 0) { $indices[] = ($indice * 11 + 6) % $productos->count(); }
                $indices = array_values(array_unique($indices));

                $detalles = collect($indices)->map(function (int $productIndex) use ($productos, $indice) {
                    $producto = $productos[$productIndex];
                    $cantidad = in_array($productIndex, [0, 3], true) ? 2 + ($indice % 2) : 1 + ($indice % 7 === 0 ? 1 : 0);
                    return ['producto_id' => $producto->id, 'descripcion' => $producto->nombre, 'cantidad' => $cantidad,
                        'precio_unitario' => (float) $producto->precio_venta, 'subtotal' => round($cantidad * (float) $producto->precio_venta, 2)];
                })->all();

                $plan[] = ['indice' => $indice, 'fecha' => $fecha, 'hora' => 9 + (($turno * 3 + $indice) % 11),
                    'metodo_pago' => match ($indice % 10) { 0, 1, 2, 3 => 'Efectivo', 4, 5, 6 => 'Débito', 7 => 'Crédito', default => 'Transferencia' },
                    'detalles' => $detalles];
            }
        }

        return $plan;
    }

    private function crearCompras(Negocio $negocio, int $userId, CarbonImmutable $hoy, $productos, $vendido): void
    {
        $proveedores = ['Distribuidora Sur', 'Alimentos Los Ríos', 'Comercial Central', 'Bebidas del Sur'];
        $stocksFinales = [28, 4, 22, 24, 18, 25, 14, 20, 18, 1, 16, 2, 3, 22, 17, 12, 15, 14, 18, 11];

        foreach ($productos as $index => $producto) {
            $cantidad = (float) ($vendido->get($producto->id, 0) + $stocksFinales[$index]);
            $fecha = $hoy->subDays(89 - ($index % 15));
            $marker = self::MARKER.sprintf('[compra:%03d]', $index);
            $compra = $negocio->compras()->updateOrCreate(['observacion' => $marker], [
                'user_id' => $userId, 'fecha' => $fecha->toDateString(), 'proveedor' => $proveedores[$index % count($proveedores)],
                'total' => round($cantidad * (float) $producto->precio_venta * .64, 2),
            ]);
            $costo = round((float) $producto->precio_venta * .64, 2);
            $compra->detalles()->updateOrCreate(['producto_id' => $producto->id], [
                'cantidad' => $cantidad, 'costo_unitario' => $costo, 'subtotal' => round($cantidad * $costo, 2),
            ]);
            StockMovimiento::query()->updateOrCreate(
                ['negocio_id' => $negocio->id, 'producto_id' => $producto->id, 'origen_tipo' => 'compra', 'origen_id' => $compra->id],
                ['user_id' => $userId, 'tipo' => 'entrada', 'cantidad' => $cantidad, 'concepto' => 'Compra #'.$compra->id, 'observacion' => self::MARKER.' Entrada automática por compra.']
            );
            $this->fechaCreacion($compra, $fecha->setTime(10, 0));
        }
    }

    /** @return array<string, Caja> */
    private function crearCajas(Negocio $negocio, int $userId, CarbonImmutable $hoy): array
    {
        $cajas = [];
        for ($offset = 74; $offset >= 0; $offset--) {
            $fecha = $hoy->subDays($offset); $index = 74 - $offset; $marker = self::MARKER.sprintf('[caja:%03d]', $index);
            $caja = $negocio->cajas()->updateOrCreate(['observacion_apertura' => $marker], [
                'user_apertura_id' => $userId, 'user_cierre_id' => $userId, 'fecha' => $fecha->toDateString(),
                'saldo_inicial' => 100000, 'abierta_en' => $fecha->setTime(8, 30), 'cerrada_en' => $fecha->setTime(21, 30),
                'estado' => 'cerrada', 'observacion_cierre' => self::MARKER.' Cierre cuadrado de jornada.',
            ]);
            $cajas[$fecha->toDateString()] = $caja;
        }
        return $cajas;
    }

    private function crearVentas(Negocio $negocio, int $adminId, int $vendedorId, array $plan, array $cajas): void
    {
        $contador = VentaContador::query()->firstOrCreate(['negocio_id' => $negocio->id], ['ultimo_numero' => 0]);
        $ultimoNumero = max((int) $contador->ultimo_numero, (int) $negocio->ventas()->max('numero_documento_interno'));

        foreach ($plan as $item) {
            $token = Uuid::uuid5(Uuid::NAMESPACE_URL, 'acsoft:negocio-prueba:venta:'.$item['indice'])->toString();
            $venta = $negocio->ventas()->where('operation_token', $token)->first();
            if (! $venta) {
                $venta = $negocio->ventas()->create(['operation_token' => $token, 'numero_documento_interno' => ++$ultimoNumero,
                    'user_id' => $item['indice'] % 6 === 0 ? $adminId : $vendedorId, 'fecha' => $item['fecha']->toDateString(),
                    'metodo_pago' => $item['metodo_pago'], 'observacion' => self::MARKER.sprintf('[venta:%03d]', $item['indice']), 'total' => 0]);
            } else {
                $venta->update(['user_id' => $item['indice'] % 6 === 0 ? $adminId : $vendedorId, 'fecha' => $item['fecha']->toDateString(), 'metodo_pago' => $item['metodo_pago']]);
            }

            $total = 0;
            foreach ($item['detalles'] as $detalle) {
                $venta->detalles()->updateOrCreate(['producto_id' => $detalle['producto_id']], $detalle);
                $total += $detalle['subtotal'];
                StockMovimiento::query()->updateOrCreate(
                    ['negocio_id' => $negocio->id, 'producto_id' => $detalle['producto_id'], 'origen_tipo' => 'venta', 'origen_id' => $venta->id],
                    ['user_id' => $venta->user_id, 'tipo' => 'salida', 'cantidad' => $detalle['cantidad'], 'concepto' => 'Venta #'.$venta->id, 'observacion' => self::MARKER.' Salida automática por venta.']
                );
            }
            $venta->update(['total' => round($total, 2)]);
            $timestamp = $item['fecha']->setTime($item['hora'], ($item['indice'] * 7) % 60); $this->fechaCreacion($venta, $timestamp);

            if ($item['metodo_pago'] === 'Efectivo') {
                CajaMovimiento::query()->updateOrCreate(['origen_tipo' => 'venta', 'origen_id' => $venta->id], [
                    'caja_id' => $cajas[$item['fecha']->toDateString()]->id, 'user_id' => $venta->user_id, 'tipo' => 'ingreso',
                    'concepto' => 'Venta #'.$venta->id, 'monto' => $venta->total, 'observacion' => self::MARKER.' Movimiento automático por venta en efectivo.',
                ]);
            }
        }
        $contador->update(['ultimo_numero' => max($ultimoNumero, (int) $negocio->ventas()->max('numero_documento_interno'))]);
    }

    private function crearGastos(Negocio $negocio, int $adminId, int $vendedorId, CarbonImmutable $hoy, array $cajas): void
    {
        $conceptos = [['Luz','Servicios',38000],['Agua','Servicios',18000],['Gas','Servicios',26000],['Transporte','Logística',8500],
            ['Insumos de embalaje','Insumos',12500],['Mantención de equipos','Mantención',32000],['Artículos de limpieza','Limpieza',7900],
            ['Internet','Servicios',18990],['Flete de mercadería','Logística',14000],['Otros gastos operacionales','Otros',6000]];

        for ($index = 0; $index < 45; $index++) {
            $fecha = $hoy->subDays(74 - (int) floor($index * 74 / 44)); $base = $conceptos[$index % count($conceptos)];
            $metodo = match ($index % 5) { 0, 1 => 'Efectivo', 2, 3 => 'Débito', default => 'Transferencia' };
            $token = Uuid::uuid5(Uuid::NAMESPACE_URL, 'acsoft:negocio-prueba:gasto:'.$index)->toString();
            $gasto = Gasto::query()->updateOrCreate(['negocio_id' => $negocio->id, 'operation_token' => $token], [
                'user_id' => $index % 5 === 0 ? $vendedorId : $adminId, 'fecha' => $fecha->toDateString(), 'concepto' => $base[0],
                'monto' => $base[2] + (($index % 4) * 1250), 'categoria' => $base[1], 'metodo_pago' => $metodo,
                'observacion' => self::MARKER.sprintf('[gasto:%03d]', $index),
            ]);
            $this->fechaCreacion($gasto, $fecha->setTime(12 + ($index % 6), ($index * 11) % 60));
            if ($metodo === 'Efectivo') {
                CajaMovimiento::query()->updateOrCreate(['origen_tipo' => 'gasto', 'origen_id' => $gasto->id], [
                    'caja_id' => $cajas[$fecha->toDateString()]->id, 'user_id' => $gasto->user_id, 'tipo' => 'egreso',
                    'concepto' => 'Gasto #'.$gasto->id, 'monto' => $gasto->monto, 'observacion' => self::MARKER.' Movimiento automático por gasto en efectivo.',
                ]);
            }
        }
    }

    private function cerrarCajas(array $cajas, int $userId): void
    {
        foreach ($cajas as $caja) {
            $ingresos = (float) $caja->movimientos()->where('tipo', 'ingreso')->sum('monto');
            $egresos = (float) $caja->movimientos()->where('tipo', 'egreso')->sum('monto');
            $esperado = round((float) $caja->saldo_inicial + $ingresos - $egresos, 2);
            $caja->update(['user_cierre_id' => $userId, 'saldo_esperado' => $esperado, 'saldo_contado' => $esperado, 'diferencia' => 0, 'estado' => 'cerrada']);
        }
    }

    private function fechaCreacion($model, CarbonImmutable $timestamp): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->saveQuietly();
        $model->timestamps = true;
    }
}
