<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\DemoProducto;
use App\Models\DemoProveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(): View
    {
        $productos = DemoProducto::where(
            'demo_session_id',
            session('demo_session_id')
        )
        ->with('proveedores')
        ->latest()
        ->get();

        return view(
            'demo.productos.index',
            compact('productos')
        );
    }


    public function create(): View
    {
        $proveedores = $this->proveedoresDeSesion();

        return view(
            'demo.productos.create',
            compact('proveedores')
        );
    }


    public function store(Request $request): RedirectResponse
    {
        $proveedoresValidos = $this
            ->proveedoresDeSesion()
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'unidad' => [
                'required',
                Rule::in([
                    'kg',
                    'unidad',
                    'caja',
                ]),
            ],

            'proveedores' => [
                'nullable',
                'array',
            ],

            'proveedores.*' => [
                'integer',
                Rule::in($proveedoresValidos),
            ],
        ]);

        $producto = DemoProducto::create([
            'demo_session_id' => session('demo_session_id'),
            'nombre' => $validated['nombre'],
            'unidad' => $validated['unidad'],
        ]);

        $producto->proveedores()->sync(
            $validated['proveedores'] ?? []
        );

        return redirect()
            ->route('demo.productos.index')
            ->with(
                'success',
                'Producto registrado correctamente.'
            );
    }


    public function edit(DemoProducto $producto): View
    {
        $this->ensureBelongsToCurrentDemo($producto);

        $producto->load('proveedores');

        $proveedores = $this->proveedoresDeSesion();

        return view(
            'demo.productos.edit',
            compact(
                'producto',
                'proveedores'
            )
        );
    }


    public function update(
        Request $request,
        DemoProducto $producto
    ): RedirectResponse {

        $this->ensureBelongsToCurrentDemo($producto);

        $proveedoresValidos = $this
            ->proveedoresDeSesion()
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'unidad' => [
                'required',
                Rule::in([
                    'kg',
                    'unidad',
                    'caja',
                ]),
            ],

            'proveedores' => [
                'nullable',
                'array',
            ],

            'proveedores.*' => [
                'integer',
                Rule::in($proveedoresValidos),
            ],
        ]);

        $producto->update([
            'nombre' => $validated['nombre'],
            'unidad' => $validated['unidad'],
        ]);

        $producto->proveedores()->sync(
            $validated['proveedores'] ?? []
        );

        return redirect()
            ->route('demo.productos.index')
            ->with(
                'success',
                'Producto actualizado correctamente.'
            );
    }


    public function destroy(
        DemoProducto $producto
    ): RedirectResponse {

        $this->ensureBelongsToCurrentDemo($producto);

        $producto->delete();

        return redirect()
            ->route('demo.productos.index')
            ->with(
                'success',
                'Producto eliminado correctamente.'
            );
    }


    private function proveedoresDeSesion()
    {
        return DemoProveedor::where(
            'demo_session_id',
            session('demo_session_id')
        )
        ->orderBy('nombre')
        ->get();
    }


    private function ensureBelongsToCurrentDemo(
        DemoProducto $producto
    ): void {

        abort_unless(
            $producto->demo_session_id
                === session('demo_session_id'),
            404
        );
    }
}