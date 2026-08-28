<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(
        Request $request,
        Negocio $negocio
    ): View {

        $productos = $negocio
            ->productos()
            ->where('activo', true)
            ->withStockActual()
            ->orderBy('nombre')
            ->get();

        return view(
            'gestion.stock.index',
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
            'producto_id' => [
                'required',
                'integer',

                Rule::exists(
                    'productos',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'negocio_id',
                            $negocio->id
                        )
                ),
            ],

            'tipo' => [
                'required',
                Rule::in([
                    'entrada',
                    'salida',
                    'ajuste',
                ]),
            ],

            'cantidad' => [
                'required',
                'numeric',
                'not_in:0',
            ],

            'concepto' => [
                'required',
                'string',
                'max:255',
            ],

            'observacion' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        $cantidad = (float) $validated['cantidad'];

        if (in_array($validated['tipo'], ['entrada', 'salida'])) {
            $cantidad = abs($cantidad);
        }

        DB::transaction(function () use (
            $validated,
            $request,
            $negocio,
            $cantidad
        ) {
            $producto = $negocio
                ->productos()
                ->whereKey($validated['producto_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $validated['tipo'] === 'salida'
                && $producto->stockActual() < $cantidad
            ) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No hay stock suficiente para registrar esta salida.',
                ]);
            }

            $negocio->movimientosStock()->create([
                'producto_id' => $producto->id,
                'user_id' => $request->user()->id,
                'tipo' => $validated['tipo'],
                'cantidad' => $cantidad,
                'concepto' => $validated['concepto'],
                'observacion' => $validated['observacion'] ?? null,
            ]);
        });

        return redirect()
            ->route(
                'gestion.stock.index',
                $negocio
            )
            ->with(
                'success',
                'Movimiento de stock registrado correctamente.'
            );
    }


    public function history(
        Request $request,
        Negocio $negocio
    ): View {

        $query = $negocio
            ->movimientosStock()
            ->with([
                'producto',
                'usuario',
            ])
            ->latest();


        if ($request->filled('producto_id')) {
            $query->where(
                'producto_id',
                $request->input('producto_id')
            );
        }


        $movimientos = $query
            ->paginate(30)
            ->withQueryString();


        $productos = $negocio
            ->productos()
            ->orderBy('nombre')
            ->get();


        return view(
            'gestion.stock.history',
            compact(
                'negocio',
                'movimientos',
                'productos'
            )
        );
    }
}