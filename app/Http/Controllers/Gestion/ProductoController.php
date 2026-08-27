<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(
        Request $request,
        Negocio $negocio
    ): View {

        $query = $negocio
            ->productos()
            ->orderBy('nombre');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');

            $query->where(function ($q) use ($buscar) {
                $q->where(
                    'nombre',
                    'like',
                    '%' . $buscar . '%'
                )
                ->orWhere(
                    'codigo',
                    'like',
                    '%' . $buscar . '%'
                );
            });
        }

        $productos = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'gestion.productos.index',
            compact(
                'negocio',
                'productos'
            )
        );
    }


    public function create(
        Negocio $negocio
    ): View {

        return view(
            'gestion.productos.create',
            compact('negocio')
        );
    }


    public function store(
        Request $request,
        Negocio $negocio
    ): RedirectResponse {

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'codigo' => [
                'nullable',
                'string',
                'max:100',

                Rule::unique(
                    'productos',
                    'codigo'
                )
                ->where(
                    fn ($query) =>
                        $query->where(
                            'negocio_id',
                            $negocio->id
                        )
                ),
            ],

            'unidad' => [
                'required',
                Rule::in([
                    'unidad',
                    'kg',
                    'g',
                    'litro',
                    'ml',
                    'caja',
                    'paquete',
                ]),
            ],

            'precio_venta' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'stock_minimo' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);


        $negocio->productos()->create([
            ...$validated,
            'activo' => true,
        ]);


        return redirect()
            ->route(
                'gestion.productos.index',
                $negocio
            )
            ->with(
                'success',
                'Producto creado correctamente.'
            );
    }


    public function edit(
        Negocio $negocio,
        Producto $producto
    ): View {

        abort_unless(
            $producto->negocio_id === $negocio->id,
            404
        );

        return view(
            'gestion.productos.edit',
            compact(
                'negocio',
                'producto'
            )
        );
    }


    public function update(
        Request $request,
        Negocio $negocio,
        Producto $producto
    ): RedirectResponse {

        abort_unless(
            $producto->negocio_id === $negocio->id,
            404
        );


        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'codigo' => [
                'nullable',
                'string',
                'max:100',

                Rule::unique(
                    'productos',
                    'codigo'
                )
                ->where(
                    fn ($query) =>
                        $query->where(
                            'negocio_id',
                            $negocio->id
                        )
                )
                ->ignore($producto->id),
            ],

            'unidad' => [
                'required',
                Rule::in([
                    'unidad',
                    'kg',
                    'g',
                    'litro',
                    'ml',
                    'caja',
                    'paquete',
                ]),
            ],

            'precio_venta' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'stock_minimo' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],
        ]);


        $producto->update([
            'nombre' =>
                $validated['nombre'],

            'codigo' =>
                $validated['codigo'] ?? null,

            'unidad' =>
                $validated['unidad'],

            'precio_venta' =>
                $validated['precio_venta'] ?? null,

            'stock_minimo' =>
                $validated['stock_minimo'] ?? null,

            'activo' =>
                $request->boolean('activo'),
        ]);


        return redirect()
            ->route(
                'gestion.productos.index',
                $negocio
            )
            ->with(
                'success',
                'Producto actualizado correctamente.'
            );
    }


    public function destroy(
        Negocio $negocio,
        Producto $producto
    ): RedirectResponse {

        abort_unless(
            $producto->negocio_id === $negocio->id,
            404
        );

        $producto->delete();

        return redirect()
            ->route(
                'gestion.productos.index',
                $negocio
            )
            ->with(
                'success',
                'Producto eliminado correctamente.'
            );
    }
}