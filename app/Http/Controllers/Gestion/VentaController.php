<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\CajaMovimiento;
use App\Models\Negocio;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(
        Request $request,
        Negocio $negocio
    ): View {

        $query = $negocio
            ->ventas()
            ->with([
                'usuario',
                'detalles.producto',
            ])
            ->orderByDesc('fecha')
            ->orderByDesc('id');


        if ($request->filled('desde')) {

            $query->whereDate(
                'fecha',
                '>=',
                $request->input('desde')
            );
        }


        if ($request->filled('hasta')) {

            $query->whereDate(
                'fecha',
                '<=',
                $request->input('hasta')
            );
        }


        $ventas = $query
            ->paginate(20)
            ->withQueryString();


        $totalHoy = $negocio
            ->ventas()
            ->whereDate(
                'fecha',
                now()->toDateString()
            )
            ->sum('total');


        return view(
            'gestion.ventas.index',
            compact(
                'negocio',
                'ventas',
                'totalHoy'
            )
        );
    }


    public function create(
        Negocio $negocio
    ): View {

        $usaProductos =
            $negocio->tieneModulo('productos');


        $usaStock =
            $negocio->tieneModulo('stock');


        $productos = collect();


        if ($usaProductos) {

            $productos = $negocio
                ->productos()
                ->where('activo', true)
                ->when($usaStock, fn ($query) => $query->withStockActual())
                ->orderBy('nombre')
                ->get();

        }


        return view(
            'gestion.ventas.create',
            compact(
                'negocio',
                'productos',
                'usaProductos',
                'usaStock'
            )
        );
    }


    public function store(
        Request $request,
        Negocio $negocio
    ): RedirectResponse {

        $usaProductos =
            $negocio->tieneModulo('productos');

        $usaStock =
            $negocio->tieneModulo('stock');


        $rules = [
            'fecha' => [
                'required',
                'date',
            ],

            'metodo_pago' => [
                'nullable',
                'string',
                'max:50',
            ],

            'observacion' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'detalles' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'detalles.*.descripcion' => [
                'required',
                'string',
                'max:255',
            ],

            'detalles.*.cantidad' => [
                'required',
                'numeric',
                'min:0.001',
            ],

            'detalles.*.precio_unitario' => [
                'required',
                'numeric',
                'min:0',
            ],

            'detalles.*.producto_id' => [
                'nullable',
                'integer',
            ],
        ];


        if ($usaProductos) {

            $rules['detalles.*.producto_id'] = [
                'nullable',
                'integer',

                Rule::exists(
                    'productos',
                    'id'
                )->where(
                    fn($query) =>
                    $query->where(
                        'negocio_id',
                        $negocio->id
                    )
                ),
            ];
        }


        $validated =
            $request->validate($rules);


        $cantidadesPorProducto = collect($validated['detalles'])
            ->filter(fn (array $detalle) => !empty($detalle['producto_id']))
            ->groupBy(fn (array $detalle) => (int) $detalle['producto_id'])
            ->map(fn ($detalles) => $detalles->sum(
                fn (array $detalle) => (float) $detalle['cantidad']
            ));

        DB::transaction(function () use (
            $validated,
            $request,
            $negocio,
            $usaProductos,
            $usaStock,
            $cantidadesPorProducto
        ) {
            $productos = collect();

            if ($usaProductos && $cantidadesPorProducto->isNotEmpty()) {
                $productoIds = $cantidadesPorProducto
                    ->keys()
                    ->sort()
                    ->values();

                $productos = $negocio
                    ->productos()
                    ->whereIn('id', $productoIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($productos->count() !== $productoIds->count()) {
                    throw ValidationException::withMessages([
                        'detalles' => 'Uno de los productos no pertenece al negocio.',
                    ]);
                }

                if ($usaStock) {
                    $stocks = DB::table('stock_movimientos')
                        ->select('producto_id')
                        ->selectRaw(Producto::stockExpression().' as stock_actual')
                        ->whereIn('producto_id', $productoIds)
                        ->groupBy('producto_id')
                        ->pluck('stock_actual', 'producto_id');

                    foreach ($cantidadesPorProducto as $productoId => $cantidad) {
                        $disponible = (float) ($stocks[$productoId] ?? 0);

                        if ($disponible < $cantidad) {
                            $producto = $productos->get($productoId);

                            throw ValidationException::withMessages([
                                'detalles' => 'Stock insuficiente para '
                                    .$producto->nombre.'. Disponible: '
                                    .$disponible.' '.$producto->unidad.'.',
                            ]);
                        }
                    }
                }
            }

            $venta = $negocio->ventas()->create([
                'user_id' => $request->user()->id,
                'fecha' => $validated['fecha'],
                'metodo_pago' => $validated['metodo_pago'] ?? null,
                'observacion' => $validated['observacion'] ?? null,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($validated['detalles'] as $detalle) {
                $producto = null;

                if ($usaProductos && !empty($detalle['producto_id'])) {
                    $producto = $productos->get((int) $detalle['producto_id']);
                }

                $cantidad = (float) $detalle['cantidad'];
                $precio = (float) $detalle['precio_unitario'];
                $subtotal = round($cantidad * $precio, 2);

                $venta->detalles()->create([
                    'producto_id' => $producto?->id,
                    'descripcion' => $detalle['descripcion'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;

                if ($producto && $usaStock) {
                    $negocio->movimientosStock()->create([
                        'producto_id' => $producto->id,
                        'user_id' => $request->user()->id,
                        'tipo' => 'salida',
                        'cantidad' => $cantidad,
                        'concepto' => 'Venta #'.$venta->id,
                        'origen_tipo' => 'venta',
                        'origen_id' => $venta->id,
                        'observacion' => 'Salida automática por venta.',
                    ]);
                }
            }

            $venta->update([
                'total' => round($total, 2),
            ]);

            if ($venta->metodo_pago === 'Efectivo') {
                $caja = $negocio
                    ->cajas()
                    ->where('estado', 'abierta')
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if ($caja) {
                    $caja->movimientos()->create([
                        'user_id' => $request->user()->id,
                        'tipo' => 'ingreso',
                        'concepto' => 'Venta #'.$venta->id,
                        'monto' => $venta->total,
                        'observacion' => 'Movimiento automático por venta en efectivo.',
                        'origen_tipo' => 'venta',
                        'origen_id' => $venta->id,
                    ]);
                }
            }
        });

        return redirect()
            ->route(
                'gestion.ventas.index',
                $negocio
            )
            ->with(
                'success',
                'Venta registrada correctamente.'
            );
    }


    public function destroy(
        Negocio $negocio,
        Venta $venta
    ): RedirectResponse {

        abort_unless(
            $venta->negocio_id
                === $negocio->id,
            404
        );


        $eliminada = DB::transaction(function () use ($negocio, $venta) {
            $movimientoCaja = CajaMovimiento::query()
                ->where('origen_tipo', 'venta')
                ->where('origen_id', $venta->id)
                ->whereHas(
                    'caja',
                    fn ($query) => $query->where('negocio_id', $negocio->id)
                )
                ->first();

            if ($movimientoCaja) {
                $caja = $negocio
                    ->cajas()
                    ->whereKey($movimientoCaja->caja_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$caja->estaAbierta()) {
                    return false;
                }
            }

            $negocio
                ->movimientosStock()
                ->where('origen_tipo', 'venta')
                ->where('origen_id', $venta->id)
                ->delete();

            $movimientoCaja?->delete();
            $venta->delete();

            return true;
        });

        if (!$eliminada) {
            return back()->with(
                'error',
                'No se puede eliminar la venta porque pertenece a una caja cerrada.'
            );
        }

        return redirect()
            ->route(
                'gestion.ventas.index',
                $negocio
            )
            ->with(
                'success',
                'Venta eliminada correctamente.'
            );
    }
}
