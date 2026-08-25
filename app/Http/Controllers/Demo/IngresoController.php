<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\DemoIngreso;
use App\Models\DemoProducto;
use App\Models\DemoProveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IngresoController extends Controller
{
    public function index(): View
    {
        $ingresos = DemoIngreso::where(
            'demo_session_id',
            session('demo_session_id')
        )
            ->with([
                'proveedor',
                'producto',
                'detalles',
            ])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return view(
            'demo.ingresos.index',
            compact('ingresos')
        );
    }


    public function create(): View
    {
        $proveedores = DemoProveedor::where(
            'demo_session_id',
            session('demo_session_id')
        )
            ->with([
                'productos' => function ($query) {
                    $query
                        ->where(
                            'demo_session_id',
                            session('demo_session_id')
                        )
                        ->orderBy('nombre');
                },
            ])
            ->orderBy('nombre')
            ->get();


        $productosPorProveedor = $proveedores
            ->mapWithKeys(function ($proveedor) {

                return [
                    $proveedor->id => $proveedor->productos
                        ->map(function ($producto) {

                            return [
                                'id' => $producto->id,
                                'nombre' => $producto->nombre,
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];
            })
            ->toArray();


        return view(
            'demo.ingresos.create',
            compact(
                'proveedores',
                'productosPorProveedor'
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $sessionId = session('demo_session_id');

        $proveedoresValidos = DemoProveedor::where(
            'demo_session_id',
            $sessionId
        )
            ->pluck('id')
            ->all();

        $productosValidos = DemoProducto::where(
            'demo_session_id',
            $sessionId
        )
            ->pluck('id')
            ->all();


        $validated = $request->validate([
            'proveedor_id' => [
                'required',
                'integer',
                Rule::in($proveedoresValidos),
            ],

            'producto_id' => [
                'required',
                'integer',
                Rule::in($productosValidos),
            ],

            'fecha' => [
                'required',
                'date',
            ],

            'cantidad_cajas' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'pesos' => [
                'required',
                'array',
            ],

            'pesos.*' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999.99',
            ],
        ], [
            'proveedor_id.required' =>
            'Selecciona un proveedor.',

            'producto_id.required' =>
            'Selecciona un producto.',

            'cantidad_cajas.required' =>
            'Indica la cantidad de cajas.',

            'cantidad_cajas.min' =>
            'Debes registrar al menos una caja.',

            'pesos.required' =>
            'Ingresa el peso de las cajas.',

            'pesos.*.required' =>
            'Todas las cajas deben tener un peso.',

            'pesos.*.min' =>
            'El peso debe ser mayor a cero.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validar relación proveedor-producto
        |--------------------------------------------------------------------------
        */

        $proveedor = DemoProveedor::where(
            'demo_session_id',
            $sessionId
        )
            ->findOrFail($validated['proveedor_id']);

        $productoValido = $proveedor
            ->productos()
            ->where(
                'demo_productos.id',
                $validated['producto_id']
            )
            ->exists();

        if (!$productoValido) {
            return back()
                ->withErrors([
                    'producto_id' =>
                    'El producto seleccionado no pertenece a este proveedor.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Validar número de pesos
        |--------------------------------------------------------------------------
        */

        if (
            count($validated['pesos'])
            !== (int) $validated['cantidad_cajas']
        ) {
            return back()
                ->withErrors([
                    'pesos' =>
                    'La cantidad de pesos no coincide con la cantidad de cajas.',
                ])
                ->withInput();
        }


        $pesoTotal = collect($validated['pesos'])
            ->sum(fn($peso) => (float) $peso);


        DB::transaction(function () use (
            $validated,
            $sessionId,
            $pesoTotal
        ) {

            $ingreso = DemoIngreso::create([
                'demo_session_id' => $sessionId,

                'demo_proveedor_id' =>
                $validated['proveedor_id'],

                'demo_producto_id' =>
                $validated['producto_id'],

                'fecha' =>
                $validated['fecha'],

                'cantidad_cajas' =>
                $validated['cantidad_cajas'],

                'peso_total' =>
                $pesoTotal,
            ]);


            foreach (
                $validated['pesos']
                as $index => $peso
            ) {

                $ingreso->detalles()->create([
                    'numero_caja' => $index + 1,
                    'peso' => $peso,
                ]);
            }
        });


        return redirect()
            ->route('demo.ingresos.index')
            ->with(
                'success',
                'Ingreso registrado correctamente.'
            );
    }


    public function destroy(
        DemoIngreso $ingreso
    ): RedirectResponse {

        $this->ensureBelongsToCurrentDemo($ingreso);

        $ingreso->delete();

        return redirect()
            ->route('demo.ingresos.index')
            ->with(
                'success',
                'Ingreso eliminado correctamente.'
            );
    }


    private function ensureBelongsToCurrentDemo(
        DemoIngreso $ingreso
    ): void {

        abort_unless(
            $ingreso->demo_session_id
                === session('demo_session_id'),
            404
        );
    }
}
