<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Gasto;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GastoController extends Controller
{
    public function index(
        Request $request,
        Negocio $negocio
    ): View {

        $query = $negocio
            ->gastos()
            ->with('usuario')
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


        $gastos = $query
            ->paginate(20)
            ->withQueryString();


        $totalHoy = $negocio
            ->gastos()
            ->whereDate(
                'fecha',
                now()->toDateString()
            )
            ->sum('monto');


        return view(
            'gestion.gastos.index',
            compact(
                'negocio',
                'gastos',
                'totalHoy'
            )
        );
    }


    public function create(
        Negocio $negocio
    ): View {

        return view(
            'gestion.gastos.create',
            compact('negocio')
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

            'concepto' => [
                'required',
                'string',
                'max:255',
            ],

            'monto' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'categoria' => [
                'nullable',
                'string',
                'max:100',
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
        ]);


        $gasto = $negocio->gastos()->create([
            'user_id' => $request->user()->id,
            'fecha' => $validated['fecha'],
            'concepto' => $validated['concepto'],
            'monto' => $validated['monto'],
            'categoria' => $validated['categoria'] ?? null,
            'metodo_pago' => $validated['metodo_pago'] ?? null,
            'observacion' => $validated['observacion'] ?? null,
        ]);

        if ($gasto->metodo_pago === 'Efectivo') {

            $caja = $negocio->cajaAbierta();

            if ($caja) {

                $caja->movimientos()->create([
                    'user_id' => $request->user()->id,
                    'tipo' => 'egreso',
                    'concepto' => 'Gasto #' . $gasto->id,
                    'monto' => $gasto->monto,
                    'observacion' => 'Movimiento automático por gasto en efectivo.',
                    'origen_tipo' => 'gasto',
                    'origen_id' => $gasto->id,
                ]);
            }
        }


        return redirect()
            ->route(
                'gestion.gastos.index',
                $negocio
            )
            ->with(
                'success',
                'Gasto registrado correctamente.'
            );
    }


    public function destroy(
        Negocio $negocio,
        Gasto $gasto
    ): RedirectResponse {

        abort_unless(
            $gasto->negocio_id === $negocio->id,
            404
        );

        $negocio
            ->cajas()
            ->with('movimientos')
            ->get()
            ->each(function ($caja) use ($gasto) {

                $caja->movimientos()
                    ->where('origen_tipo', 'gasto')
                    ->where('origen_id', $gasto->id)
                    ->delete();
            });

        $gasto->delete();


        return redirect()
            ->route(
                'gestion.gastos.index',
                $negocio
            )
            ->with(
                'success',
                'Gasto eliminado correctamente.'
            );
    }
}
