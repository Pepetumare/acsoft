<?php

namespace App\Support;

use App\Models\Gasto;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BusinessAnalytics
{
    /** @return array<string, mixed> */
    public function for(Negocio $negocio, CarbonInterface $desde, CarbonInterface $hasta): array
    {
        $sales = Venta::query()->where('negocio_id', $negocio->id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);
        $expenses = Gasto::query()->where('negocio_id', $negocio->id)
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()]);
        $salesByDate = (clone $sales)->selectRaw('fecha, SUM(total) as total, COUNT(*) as cantidad')
            ->groupBy('fecha')->orderBy('fecha')->get()->keyBy(fn ($row) => CarbonImmutable::parse($row->fecha)->toDateString());
        $expensesByDate = (clone $expenses)->selectRaw('fecha, SUM(monto) as total')
            ->groupBy('fecha')->orderBy('fecha')->get()->keyBy(fn ($row) => CarbonImmutable::parse($row->fecha)->toDateString());
        $ventasTotal = (float) $salesByDate->sum('total');
        $gastosTotal = (float) $expensesByDate->sum('total');
        $cantidadVentas = (int) $salesByDate->sum('cantidad');

        $details = VentaDetalle::query()->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->where('ventas.negocio_id', $negocio->id)->whereBetween('ventas.fecha', [$desde->toDateString(), $hasta->toDateString()]);
        $productosVendidos = (float) (clone $details)->sum('venta_detalles.cantidad');
        $topProducts = (clone $details)->selectRaw('venta_detalles.descripcion, SUM(venta_detalles.cantidad) as cantidad, SUM(venta_detalles.subtotal) as total')
            ->groupBy('venta_detalles.descripcion')->orderByDesc('cantidad')->limit(10)->get();
        $paymentMethods = (clone $sales)
            ->selectRaw('metodo_pago, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('metodo_pago')->orderByDesc('total')->get()
            ->groupBy(fn ($row) => filled(trim((string) $row->metodo_pago)) ? trim((string) $row->metodo_pago) : 'Sin especificar')
            ->map(function (Collection $rows, string $method) {
                return (object) ['metodo' => $method, 'cantidad' => $rows->sum('cantidad'), 'total' => $rows->sum('total')];
            })->values();
        $stockCritico = Producto::query()->where('negocio_id', $negocio->id)->where('activo', true)
            ->whereNotNull('stock_minimo')->withStockActual()->get()
            ->filter(fn (Producto $product) => $product->stockActual() <= (float) $product->stock_minimo)
            ->sortBy('stock_actual')->take(10)->values();

        return [
            'kpis' => ['ventas_total' => $ventasTotal, 'gastos_total' => $gastosTotal,
                'resultado' => $ventasTotal - $gastosTotal, 'ticket_promedio' => $cantidadVentas ? $ventasTotal / $cantidadVentas : 0,
                'cantidad_ventas' => $cantidadVentas, 'productos_vendidos' => $productosVendidos],
            'charts' => [
                'timeline' => $this->timeline($desde, $hasta, $salesByDate, $expensesByDate),
                'products' => ['labels' => $topProducts->pluck('descripcion')->all(), 'quantities' => $topProducts->pluck('cantidad')->map(fn ($v) => (float) $v)->all(), 'totals' => $topProducts->pluck('total')->map(fn ($v) => (float) $v)->all()],
                'payments' => ['labels' => $paymentMethods->pluck('metodo')->map(fn ($v) => Str::headline($v))->all(), 'totals' => $paymentMethods->pluck('total')->map(fn ($v) => (float) $v)->all()],
                'weekdays' => $this->weekdays($salesByDate),
            ],
            'stockCritico' => $stockCritico,
            'insights' => $this->insights($ventasTotal, $gastosTotal, $topProducts, $paymentMethods, $salesByDate),
            'hasActivity' => $cantidadVentas > 0 || $gastosTotal > 0,
        ];
    }

    private function timeline(CarbonInterface $from, CarbonInterface $to, Collection $sales, Collection $expenses): array
    {
        $labels = $saleValues = $expenseValues = [];
        for ($date = CarbonImmutable::parse($from); $date->lte($to); $date = $date->addDay()) {
            $key = $date->toDateString(); $labels[] = $date->format('d/m');
            $saleValues[] = (float) ($sales->get($key)?->total ?? 0); $expenseValues[] = (float) ($expenses->get($key)?->total ?? 0);
        }
        return compact('labels', 'saleValues', 'expenseValues');
    }

    private function weekdays(Collection $sales): array
    {
        $labels = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']; $totals = array_fill(0, 7, 0.0);
        foreach ($sales as $date => $row) { $totals[CarbonImmutable::parse($date)->dayOfWeekIso - 1] += (float) $row->total; }
        return compact('labels', 'totals');
    }

    private function insights(float $sales, float $expenses, Collection $products, Collection $payments, Collection $salesByDate): array
    {
        $product = $products->first(); $payment = $payments->sortByDesc('cantidad')->first(); $day = $salesByDate->sortByDesc('total')->first();
        return [
            $product ? "El producto más vendido fue {$product->descripcion}, con ".number_format((float) $product->cantidad, 3, ',', '.').' unidades.' : 'Aún no hay productos vendidos en este período.',
            $day ? 'El día con mayores ventas fue '.CarbonImmutable::parse($day->fecha)->locale('es')->translatedFormat('l d \d\e F').'.' : 'Aún no hay un día de ventas destacado.',
            $payment ? 'El método de pago más usado fue '.Str::headline($payment->metodo).'.' : 'Aún no hay métodos de pago registrados.',
            $sales > 0 ? 'Los gastos representan '.number_format(($expenses / $sales) * 100, 1, ',', '.').'% de las ventas.' : 'Se necesitan ventas para calcular la proporción de gastos.',
        ];
    }
}
