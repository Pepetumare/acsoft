<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
                ->orderBy('nombre')
                ->get();

            if ($usaStock) {

                $productos->each(
                    function ($producto) {

                        $producto->stock_actual =
                            $producto->stockActual();
                    }
                );
            }
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


        /*
        |--------------------------------------------------------------------------
        | Validar stock antes de crear la venta
        |--------------------------------------------------------------------------
        |
        | Agrupamos productos porque una misma venta podría incluir
        | el mismo producto más de una vez.
        |
        */

        if ($usaProductos && $usaStock) {

            $cantidadesPorProducto = [];


            foreach (
                $validated['detalles']
                as $detalle
            ) {

                if (
                    empty($detalle['producto_id'])
                ) {
                    continue;
                }


                $productoId =
                    (int) $detalle['producto_id'];


                $cantidadesPorProducto[$productoId] =
                    (
                        $cantidadesPorProducto[$productoId] ?? 0
                    )
                    +
                    (float)
                    $detalle['cantidad'];
            }


            foreach (
                $cantidadesPorProducto
                as $productoId => $cantidad
            ) {

                $producto = $negocio
                    ->productos()
                    ->findOrFail(
                        $productoId
                    );


                if (
                    $producto->stockActual()
                    < $cantidad
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Stock insuficiente para '
                                . $producto->nombre
                                . '. Disponible: '
                                . $producto->stockActual()
                                . ' '
                                . $producto->unidad
                                . '.'
                        );
                }
            }
        }


        DB::transaction(function () use (
            $validated,
            $request,
            $negocio,
            $usaProductos,
            $usaStock
        ) {

            $venta = $negocio
                ->ventas()
                ->create([
                    'user_id' =>
                    $request->user()->id,

                    'fecha' =>
                    $validated['fecha'],

                    'metodo_pago' =>
                    $validated['metodo_pago']
                        ?? null,

                    'observacion' =>
                    $validated['observacion']
                        ?? null,

                    'total' =>
                    0,
                ]);


            $total = 0;


            foreach (
                $validated['detalles']
                as $detalle
            ) {

                $producto = null;


                if (
                    $usaProductos
                    && !empty($detalle['producto_id'])
                ) {

                    $producto = $negocio
                        ->productos()
                        ->findOrFail(
                            $detalle['producto_id']
                        );
                }


                $cantidad =
                    (float)
                    $detalle['cantidad'];

                $precio =
                    (float)
                    $detalle['precio_unitario'];

                $subtotal = round(
                    $cantidad * $precio,
                    2
                );


                $venta
                    ->detalles()
                    ->create([
                        'producto_id' =>
                        $producto?->id,

                        'descripcion' =>
                        $detalle['descripcion'],

                        'cantidad' =>
                        $cantidad,

                        'precio_unitario' =>
                        $precio,

                        'subtotal' =>
                        $subtotal,
                    ]);


                $total += $subtotal;


                /*
                |--------------------------------------------------------------------------
                | Salida automática de stock
                |--------------------------------------------------------------------------
                */

                if (
                    $producto
                    && $usaStock
                ) {

                    $negocio
                        ->movimientosStock()
                        ->create([
                            'producto_id' =>
                            $producto->id,

                            'user_id' =>
                            $request
                                ->user()
                                ->id,

                            'tipo' =>
                            'salida',

                            'cantidad' =>
                            $cantidad,

                            'concepto' =>
                            'Venta #'
                                . $venta->id,

                            'origen_tipo' =>
                            'venta',

                            'origen_id' =>
                            $venta->id,

                            'observacion' =>
                            'Salida automática por venta.',
                        ]);
                }
            }


            $venta->update([
                'total' =>
                round(
                    $total,
                    2
                ),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Movimiento automático de Caja
            |--------------------------------------------------------------------------
            */

            if (
                $venta->metodo_pago
                === 'Efectivo'
            ) {

                $caja =
                    $negocio->cajaAbierta();


                if ($caja) {

                    $caja
                        ->movimientos()
                        ->create([
                            'user_id' =>
                            $request
                                ->user()
                                ->id,

                            'tipo' =>
                            'ingreso',

                            'concepto' =>
                            'Venta #'
                                . $venta->id,

                            'monto' =>
                            $venta->total,

                            'observacion' =>
                            'Movimiento automático por venta en efectivo.',

                            'origen_tipo' =>
                            'venta',

                            'origen_id' =>
                            $venta->id,
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


        DB::transaction(function () use (
            $negocio,
            $venta
        ) {

            /*
            |--------------------------------------------------------------------------
            | Eliminar movimientos automáticos de Stock
            |--------------------------------------------------------------------------
            */

            $negocio
                ->movimientosStock()
                ->where(
                    'origen_tipo',
                    'venta'
                )
                ->where(
                    'origen_id',
                    $venta->id
                )
                ->delete();


            /*
            |--------------------------------------------------------------------------
            | Eliminar movimiento automático de Caja
            |--------------------------------------------------------------------------
            */

            $negocio
                ->cajas()
                ->with('movimientos')
                ->get()
                ->each(
                    function ($caja) use (
                        $venta
                    ) {

                        $caja
                            ->movimientos()
                            ->where(
                                'origen_tipo',
                                'venta'
                            )
                            ->where(
                                'origen_id',
                                $venta->id
                            )
                            ->delete();
                    }
                );


            $venta->delete();
        });


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
