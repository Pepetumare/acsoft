<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function index(
        Request $request,
        Negocio $negocio
    ): View {

        $query = $negocio
            ->compras()
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

        $compras = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'gestion.compras.index',
            compact(
                'negocio',
                'compras'
            )
        );
    }


    public function create(
        Negocio $negocio
    ): View {

        $productos = $negocio
            ->productos()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'gestion.compras.create',
            compact(
                'negocio',
                'productos'
            )
        );
    }


    public function store(
        Request $request,
        Negocio $negocio
    ): RedirectResponse {

        $validated = $request->validate([
            'fecha' => [
                'required',
                'date',
            ],

            'proveedor' => [
                'nullable',
                'string',
                'max:255',
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

            'detalles.*.producto_id' => [
                'required',
                'integer',
            ],

            'detalles.*.cantidad' => [
                'required',
                'numeric',
                'min:0.001',
                function (string $attribute, mixed $value, $fail) {
                    if (! preg_match('/^\d+(?:\.\d{1,3})?$/', (string) $value)) {
                        $fail('La cantidad puede tener como máximo 3 decimales.');
                    }
                },
            ],

            'detalles.*.costo_unitario' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);


        $usaStock = $negocio->tieneModulo('stock');

        $productoIds = collect($validated['detalles'])
            ->pluck('producto_id')
            ->map(fn ($productoId) => (int) $productoId)
            ->unique()
            ->sort()
            ->values();

        $cantidadesPorProducto = collect($validated['detalles'])
            ->groupBy(fn (array $detalle) => (int) $detalle['producto_id'])
            ->map(fn ($detalles) => $detalles->sum(
                fn (array $detalle) => (float) $detalle['cantidad']
            ));

        DB::transaction(function () use (
            $validated,
            $request,
            $negocio,
            $usaStock,
            $productoIds,
            $cantidadesPorProducto
        ) {
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

            foreach ($validated['detalles'] as $indice => $detalle) {
                $producto = $productos->get((int) $detalle['producto_id']);
                $cantidad = (float) $detalle['cantidad'];

                if (
                    $producto->requiereCantidadEntera()
                    && $cantidad !== floor($cantidad)
                ) {
                    throw ValidationException::withMessages([
                        "detalles.$indice.cantidad" => 'La cantidad para productos medidos en '
                            .$producto->unidad.' debe ser un número entero mayor o igual a 1.',
                    ]);
                }
            }

            $compra = $negocio->compras()->create([
                'user_id' => $request->user()->id,
                'fecha' => $validated['fecha'],
                'proveedor' => $validated['proveedor'] ?? null,
                'observacion' => $validated['observacion'] ?? null,
                'total' => 0,
            ]);

            $total = 0;

            foreach ($validated['detalles'] as $detalle) {
                $producto = $productos->get((int) $detalle['producto_id']);
                $cantidad = (float) $detalle['cantidad'];
                $costo = (float) $detalle['costo_unitario'];
                $subtotal = round($cantidad * $costo, 2);

                $compra->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'costo_unitario' => $costo,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            if ($usaStock) {
                foreach ($cantidadesPorProducto as $productoId => $cantidad) {
                    $negocio->movimientosStock()->create([
                        'producto_id' => $productoId,
                        'user_id' => $request->user()->id,
                        'tipo' => 'entrada',
                        'cantidad' => $cantidad,
                        'concepto' => 'Compra #'.$compra->id,
                        'origen_tipo' => 'compra',
                        'origen_id' => $compra->id,
                        'observacion' => 'Entrada automática por compra.',
                    ]);
                }
            }

            $compra->update([
                'total' => round($total, 2),
            ]);
        });

        return redirect()
            ->route(
                'gestion.compras.index',
                $negocio
            )
            ->with(
                'success',
                'Compra registrada correctamente.'
            );
    }


    public function destroy(
        Negocio $negocio,
        Compra $compra
    ): RedirectResponse {

        abort_unless(
            $compra->negocio_id === $negocio->id,
            404
        );


        DB::transaction(function () use (
            $negocio,
            $compra
        ) {

            $negocio
                ->movimientosStock()
                ->where(
                    'origen_tipo',
                    'compra'
                )
                ->where(
                    'origen_id',
                    $compra->id
                )
                ->delete();

            $compra->delete();
        });


        return redirect()
            ->route(
                'gestion.compras.index',
                $negocio
            )
            ->with(
                'success',
                'Compra eliminada correctamente.'
            );
    }
}
