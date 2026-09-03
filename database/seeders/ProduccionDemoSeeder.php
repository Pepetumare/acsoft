<?php

namespace Database\Seeders;

use App\Enums\MetodoPago;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\StockMovimiento;
use App\Models\User;
use App\Models\VentaContador;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class ProduccionDemoSeeder extends Seeder
{
    private const MARKER = '[ACSOFT-PRODUCTION-DEMO]';
    private const BUSINESS_SLUG = 'demo-acsoft';
    private const BUSINESS_NAME = 'Demo ACSoft';
    private const CLIENT_NAME = 'Cliente Demo ACSoft';
    private const USER_EMAIL = 'demo@acsoft.cl';
    private const USER_NAME = 'Administrador Demo ACSoft';
    private const MODULE_SLUGS = ['analitica', 'ventas', 'gastos', 'caja', 'productos', 'stock', 'compras', 'reportes'];

    public function run(): void
    {
        $this->assertProductionSafety();
        [$cliente, $negocio, $usuario] = $this->resolveIdentities();

        $this->command?->warn('Se afectará exclusivamente el negocio: '.self::BUSINESS_NAME.' ('.self::BUSINESS_SLUG.').');
        $this->command?->warn('Usuario demo: '.self::USER_EMAIL.'; rol admin; no superadmin.');

        DB::transaction(function () use ($cliente, $negocio, $usuario): void {
            $cliente = $cliente ?? Cliente::query()->create([
                'nombre' => self::CLIENT_NAME,
                'email' => null,
                'telefono' => null,
                'notas' => self::MARKER.' Cliente reservado exclusivamente para demostraciones de ACSoft.',
                'activo' => true,
            ]);

            $negocio = $negocio ?? Negocio::query()->create([
                'cliente_id' => $cliente->id,
                'nombre' => self::BUSINESS_NAME,
                'slug' => self::BUSINESS_SLUG,
                'activo' => true,
                'color_primario' => Negocio::COLOR_PRIMARIO_DEFAULT,
                'color_secundario' => Negocio::COLOR_SECUNDARIO_DEFAULT,
                'logo' => null,
                'configuracion' => ['production_demo' => true, 'marker' => self::MARKER],
            ]);

            $usuario = $usuario ?? User::query()->create([
                'name' => self::USER_NAME,
                'email' => self::USER_EMAIL,
                'password' => Hash::make($this->password()),
                'is_superadmin' => false,
            ]);

            if ($usuario->wasRecentlyCreated === false) {
                $usuario->forceFill(['password' => Hash::make($this->password()), 'is_superadmin' => false])->save();
            }

            $negocio->forceFill([
                'cliente_id' => $cliente->id,
                'nombre' => self::BUSINESS_NAME,
                'activo' => true,
                'color_primario' => Negocio::COLOR_PRIMARIO_DEFAULT,
                'color_secundario' => Negocio::COLOR_SECUNDARIO_DEFAULT,
                'logo' => null,
                'configuracion' => ['production_demo' => true, 'marker' => self::MARKER],
            ])->save();

            $negocio->usuarios()->syncWithoutDetaching([$usuario->id => ['rol' => 'admin', 'activo' => true]]);
            $this->assignModules($negocio);

            $today = CarbonImmutable::today();
            $products = $this->createProducts($negocio);
            $salesPlan = $this->salesPlan($today, $products);
            $sold = collect($salesPlan)->flatMap(fn (array $sale) => $sale['details'])
                ->groupBy('producto_id')->map(fn ($details) => (float) $details->sum('cantidad'));

            $this->createPurchases($negocio, $usuario->id, $today, $products, $sold);
            $cajas = $this->createCashRegisters($negocio, $usuario->id, $today);
            $this->createSales($negocio, $usuario->id, $salesPlan, $cajas);
            $this->createExpenses($negocio, $usuario->id, $today, $cajas);
            $this->closeCashRegisters($cajas, $usuario->id);
            $this->assertFinalStock($negocio, $products);
        }, 3);
    }

    private function assertProductionSafety(): void
    {
        if (! app()->environment('production')) {
            throw new RuntimeException('SAFETY: ProduccionDemoSeeder solo puede ejecutarse con APP_ENV=production.');
        }

        $database = (string) config('database.connections.'.config('database.default').'.database');
        $expected = trim((string) getenv('DEMO_EXPECTED_DATABASE'));
        if ($expected === '' || ! hash_equals($expected, $database)) {
            throw new RuntimeException('SAFETY: DEMO_EXPECTED_DATABASE es obligatoria y debe coincidir exactamente con la base conectada.');
        }

        if ($this->password() === '') {
            throw new RuntimeException('SAFETY: DEMO_USER_PASSWORD es obligatoria; no se creó ningún dato.');
        }

        $backupDirectory = '/var/backups/acsoft';
        $newest = is_dir($backupDirectory)
            ? collect(scandir($backupDirectory) ?: [])->reject(fn (string $name) => in_array($name, ['.', '..'], true))
                ->map(fn (string $name) => $backupDirectory.DIRECTORY_SEPARATOR.$name)
                ->filter(fn (string $path) => is_file($path))->map(fn (string $path) => filemtime($path) ?: 0)->max()
            : 0;

        if (! $newest || $newest < now()->subHours(48)->getTimestamp()) {
            throw new RuntimeException('SAFETY: no existe un archivo de backup reciente (máximo 48 horas) en /var/backups/acsoft.');
        }
    }

    private function password(): string
    {
        $password = getenv('DEMO_USER_PASSWORD');

        return $password === false ? '' : (string) $password;
    }

    /** @return array{?Cliente, ?Negocio, ?User} */
    private function resolveIdentities(): array
    {
        $cliente = Cliente::query()->where('nombre', self::CLIENT_NAME)->first();
        if ($cliente && ! str_contains((string) $cliente->notas, self::MARKER)) {
            throw new RuntimeException('SAFETY: ya existe Cliente Demo ACSoft sin la marca reservada de demostración.');
        }

        $negocio = Negocio::query()->where('slug', self::BUSINESS_SLUG)->first();
        $sameName = Negocio::query()->where('nombre', self::BUSINESS_NAME)->where('slug', '!=', self::BUSINESS_SLUG)->exists();
        if ($sameName || ($negocio && ($negocio->nombre !== self::BUSINESS_NAME || ! data_get($negocio->configuracion, 'production_demo')))) {
            throw new RuntimeException('SAFETY: existe una identidad de negocio ambigua; no se modificó ningún dato.');
        }
        if ($negocio && (! $cliente || $negocio->cliente_id !== $cliente->id)) {
            throw new RuntimeException('SAFETY: demo-acsoft no pertenece al cliente demo reservado.');
        }

        $usuario = User::query()->where('email', self::USER_EMAIL)->first();
        if ($usuario && ($usuario->name !== self::USER_NAME || $usuario->is_superadmin)) {
            throw new RuntimeException('SAFETY: demo@acsoft.cl existe pero no tiene la identidad segura esperada.');
        }
        if ($usuario && $usuario->negocios()->when($negocio, fn ($q) => $q->where('negocios.id', '!=', $negocio->id))->exists()) {
            throw new RuntimeException('SAFETY: demo@acsoft.cl está asociado a otro negocio.');
        }

        return [$cliente, $negocio, $usuario];
    }

    private function assignModules(Negocio $negocio): void
    {
        $modules = Modulo::query()->whereIn('slug', self::MODULE_SLUGS)->where('activo', true)->get();
        if ($modules->count() !== count(self::MODULE_SLUGS)) {
            throw new RuntimeException('SAFETY: falta uno o más módulos activos requeridos en el catálogo existente.');
        }
        $negocio->modulos()->syncWithoutDetaching($modules->mapWithKeys(fn (Modulo $m) => [$m->id => ['activo' => true]])->all());
    }

    private function createProducts(Negocio $negocio)
    {
        $data = [
            ['Bebida Cola 1.5 L','DEMO-COLA15',2490,12], ['Agua Mineral 1.5 L','DEMO-AGUA15',1290,8],
            ['Leche Entera 1 L','DEMO-LECHE1',1390,10], ['Pan Molde','DEMO-PAN',2290,8],
            ['Arroz 1 kg','DEMO-ARROZ1',1890,8], ['Fideos 400 g','DEMO-FIDEOS',1090,10],
            ['Aceite 1 L','DEMO-ACEITE1',3290,6], ['Azúcar 1 kg','DEMO-AZUCAR1',1490,8],
            ['Harina 1 kg','DEMO-HARINA1',1290,8], ['Café 170 g','DEMO-CAFE',5490,6],
            ['Galletas surtidas','DEMO-GALLETAS',1690,8], ['Atún en lata','DEMO-ATUN',1790,5],
            ['Detergente 750 ml','DEMO-DETERG',2990,4], ['Papel Higiénico 4 rollos','DEMO-PAPEL4',3490,5],
            ['Jugo de naranja 1 L','DEMO-JUGO1',1990,7], ['Chocolate en barra','DEMO-CHOCOLATE',1490,6],
            ['Conserva de duraznos','DEMO-CONSERVA',2690,5], ['Huevos 12 unidades','DEMO-HUEVOS12',3990,5],
            ['Lavalozas 750 ml','DEMO-LAVALOZAS',2190,5], ['Cereal 400 g','DEMO-CEREAL',3790,5],
        ];

        return collect($data)->map(fn (array $p) => $negocio->productos()->updateOrCreate(
            ['codigo' => $p[1]],
            ['nombre' => $p[0], 'unidad' => 'unidad', 'precio_venta' => $p[2], 'stock_minimo' => $p[3], 'activo' => true]
        ))->values();
    }

    private function salesPlan(CarbonImmutable $today, $products): array
    {
        $plan = [];
        $popular = [0, 0, 0, 1, 2, 3, 3, 4, 5, 10, 6, 7, 11, 14, 17, 8, 9, 12, 13, 15, 16, 18, 19];
        $index = 0;
        for ($offset = 89; $offset >= 0; $offset--) {
            $date = $today->subDays($offset);
            $daily = match ($date->dayOfWeekIso) { 5 => 4, 6 => 4, 7 => 1, default => 2 };
            if ($offset % 17 === 0) { $daily++; }
            if ($offset % 23 === 0) { $daily = 1; }
            for ($turn = 0; $turn < $daily; $turn++, $index++) {
                $productIndexes = [$popular[$index % count($popular)]];
                if ($index % 2 === 0) { $productIndexes[] = ($index * 7 + 3) % $products->count(); }
                if ($index % 5 === 0) { $productIndexes[] = ($index * 11 + 8) % $products->count(); }
                $details = collect(array_unique($productIndexes))->map(function (int $i) use ($products, $index) {
                    $product = $products[$i];
                    $quantity = in_array($i, [0, 1, 3], true) ? 2 + ($index % 2) : 1 + ($index % 11 === 0 ? 1 : 0);
                    return ['producto_id' => $product->id, 'descripcion' => $product->nombre, 'cantidad' => $quantity,
                        'precio_unitario' => (float) $product->precio_venta, 'subtotal' => round($quantity * (float) $product->precio_venta, 2)];
                })->values()->all();
                $methods = MetodoPago::cases();
                $methodIndex = match ($index % 10) { 0,1,2,3 => 0, 4,5,6 => 1, 7,8 => 2, default => 3 };
                $plan[] = ['index' => $index, 'date' => $date, 'hour' => 9 + (($turn * 3 + $index) % 11),
                    'method' => $methods[$methodIndex]->value, 'details' => $details];
            }
        }
        return $plan;
    }

    private function createPurchases(Negocio $negocio, int $userId, CarbonImmutable $today, $products, $sold): void
    {
        $suppliers = ['Distribuidora Demo Central', 'Comercial Demo Sur', 'Alimentos Demo Chile', 'Abastecedora Demo Pacífico'];
        $finalStocks = [30,24,22,25,18,26,15,20,19,3,23,2,1,4,18,13,11,12,14,10];
        foreach ($products as $index => $product) {
            $quantity = (float) $sold->get($product->id, 0) + $finalStocks[$index];
            $date = $today->subDays(89 - ($index % 20));
            $token = $this->uuid('purchase', $index);
            $purchase = $negocio->compras()->updateOrCreate(['operation_token' => $token], [
                'user_id' => $userId, 'fecha' => $date->toDateString(), 'proveedor' => $suppliers[$index % 4],
                'total' => round($quantity * (float) $product->precio_venta * .64, 2),
                'observacion' => self::MARKER.sprintf('[compra:%03d]', $index),
            ]);
            $cost = round((float) $product->precio_venta * .64, 2);
            $purchase->detalles()->updateOrCreate(['producto_id' => $product->id], [
                'cantidad' => $quantity, 'costo_unitario' => $cost, 'subtotal' => round($quantity * $cost, 2),
            ]);
            StockMovimiento::query()->updateOrCreate(
                ['negocio_id' => $negocio->id, 'producto_id' => $product->id, 'origen_tipo' => 'compra', 'origen_id' => $purchase->id],
                ['user_id' => $userId, 'tipo' => 'entrada', 'cantidad' => $quantity, 'concepto' => 'Compra #'.$purchase->id,
                    'observacion' => self::MARKER.' Entrada automática por compra.']
            );
            $this->timestamp($purchase, $date->setTime(10, 0));
        }
    }

    private function createCashRegisters(Negocio $negocio, int $userId, CarbonImmutable $today): array
    {
        $cajas = [];
        for ($offset = 89; $offset >= 0; $offset--) {
            $date = $today->subDays($offset);
            $index = 89 - $offset;
            $marker = self::MARKER.sprintf('[caja:%03d]', $index);
            $caja = $negocio->cajas()->updateOrCreate(['observacion_apertura' => $marker], [
                'user_apertura_id' => $userId, 'user_cierre_id' => $userId, 'fecha' => $date->toDateString(),
                'saldo_inicial' => 100000, 'abierta_en' => $date->setTime(8, 30), 'cerrada_en' => $date->setTime(21, 30),
                'estado' => 'cerrada', 'observacion_cierre' => self::MARKER.' Cierre cuadrado de jornada.',
            ]);
            $cajas[$date->toDateString()] = $caja;
        }
        return $cajas;
    }

    private function createSales(Negocio $negocio, int $userId, array $plan, array $cajas): void
    {
        $counter = VentaContador::query()->firstOrCreate(['negocio_id' => $negocio->id], ['ultimo_numero' => 0]);
        $lastNumber = max((int) $counter->ultimo_numero, (int) $negocio->ventas()->max('numero_documento_interno'));
        foreach ($plan as $item) {
            $token = $this->uuid('sale', $item['index']);
            $sale = $negocio->ventas()->where('operation_token', $token)->first();
            if (! $sale) {
                $sale = $negocio->ventas()->create(['operation_token' => $token, 'numero_documento_interno' => ++$lastNumber,
                    'user_id' => $userId, 'fecha' => $item['date']->toDateString(), 'metodo_pago' => $item['method'],
                    'observacion' => self::MARKER.sprintf('[venta:%03d]', $item['index']), 'total' => 0]);
            } else {
                $sale->update(['user_id' => $userId, 'fecha' => $item['date']->toDateString(), 'metodo_pago' => $item['method']]);
            }
            $total = 0;
            foreach ($item['details'] as $detail) {
                $sale->detalles()->updateOrCreate(['producto_id' => $detail['producto_id']], $detail);
                $total += $detail['subtotal'];
                StockMovimiento::query()->updateOrCreate(
                    ['negocio_id' => $negocio->id, 'producto_id' => $detail['producto_id'], 'origen_tipo' => 'venta', 'origen_id' => $sale->id],
                    ['user_id' => $userId, 'tipo' => 'salida', 'cantidad' => $detail['cantidad'], 'concepto' => 'Venta #'.$sale->id,
                        'observacion' => self::MARKER.' Salida automática por venta.']
                );
            }
            $sale->update(['total' => round($total, 2)]);
            $this->timestamp($sale, $item['date']->setTime($item['hour'], ($item['index'] * 7) % 60));
            if (MetodoPago::esEfectivo($item['method'])) {
                CajaMovimiento::query()->updateOrCreate(['origen_tipo' => 'venta', 'origen_id' => $sale->id], [
                    'caja_id' => $cajas[$item['date']->toDateString()]->id, 'user_id' => $userId, 'tipo' => 'ingreso',
                    'concepto' => 'Venta #'.$sale->id, 'monto' => $sale->total,
                    'operation_token' => $this->uuid('cash-sale', $item['index']),
                    'observacion' => self::MARKER.' Movimiento automático por venta en efectivo.',
                ]);
            }
        }
        $counter->update(['ultimo_numero' => max($lastNumber, (int) $negocio->ventas()->max('numero_documento_interno'))]);
    }

    private function createExpenses(Negocio $negocio, int $userId, CarbonImmutable $today, array $cajas): void
    {
        $items = [['Electricidad','Servicios',38000],['Agua','Servicios',18000],['Gas','Servicios',26000],
            ['Transporte','Logística',8500],['Insumos de embalaje','Insumos',12500],['Mantención de equipos','Mantención',32000],
            ['Artículos de limpieza','Limpieza',7900],['Internet','Servicios',18990],['Flete de mercadería','Logística',14000],
            ['Otros gastos operacionales','Otros',6000]];
        $methods = MetodoPago::cases();
        for ($index = 0; $index < 50; $index++) {
            $date = $today->subDays(89 - (int) floor($index * 89 / 49));
            $base = $items[$index % count($items)];
            $method = $methods[match ($index % 6) { 0,1 => 0, 2,3 => 1, 4 => 2, default => 3 }]->value;
            $expense = Gasto::query()->updateOrCreate(
                ['negocio_id' => $negocio->id, 'operation_token' => $this->uuid('expense', $index)],
                ['user_id' => $userId, 'fecha' => $date->toDateString(), 'concepto' => $base[0],
                    'monto' => $base[2] + (($index % 4) * 1250), 'categoria' => $base[1], 'metodo_pago' => $method,
                    'observacion' => self::MARKER.sprintf('[gasto:%03d]', $index)]
            );
            $this->timestamp($expense, $date->setTime(12 + ($index % 6), ($index * 11) % 60));
            if (MetodoPago::esEfectivo($method)) {
                CajaMovimiento::query()->updateOrCreate(['origen_tipo' => 'gasto', 'origen_id' => $expense->id], [
                    'caja_id' => $cajas[$date->toDateString()]->id, 'user_id' => $userId, 'tipo' => 'egreso',
                    'concepto' => 'Gasto #'.$expense->id, 'monto' => $expense->monto,
                    'operation_token' => $this->uuid('cash-expense', $index),
                    'observacion' => self::MARKER.' Movimiento automático por gasto en efectivo.',
                ]);
            }
        }
    }

    private function closeCashRegisters(array $cajas, int $userId): void
    {
        foreach ($cajas as $caja) {
            $expected = round((float) $caja->saldo_inicial
                + (float) $caja->movimientos()->where('tipo', 'ingreso')->sum('monto')
                - (float) $caja->movimientos()->where('tipo', 'egreso')->sum('monto'), 2);
            $caja->update(['user_cierre_id' => $userId, 'saldo_esperado' => $expected, 'saldo_contado' => $expected,
                'diferencia' => 0, 'estado' => 'cerrada']);
        }
    }

    private function assertFinalStock(Negocio $negocio, $products): void
    {
        foreach ($products as $product) {
            if ($product->fresh()->stockActual() < 0) {
                throw new RuntimeException('SAFETY: el plan produciría stock negativo para '.$product->codigo.'.');
            }
        }
        if ($negocio->cajas()->where('estado', 'abierta')->exists()) {
            throw new RuntimeException('SAFETY: quedó una caja demo abierta; se revirtió toda la transacción.');
        }
    }

    private function uuid(string $type, int $index): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_URL, 'acsoft:production-demo:'.$type.':'.$index)->toString();
    }

    private function timestamp($model, CarbonImmutable $timestamp): void
    {
        $model->timestamps = false;
        $model->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->saveQuietly();
        $model->timestamps = true;
    }
}
