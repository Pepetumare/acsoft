<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\DemoProveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(): View
    {
        $proveedores = DemoProveedor::where(
            'demo_session_id',
            session('demo_session_id')
        )
        ->latest()
        ->get();

        return view(
            'demo.proveedores.index',
            compact('proveedores')
        );
    }


    public function create(): View
    {
        return view('demo.proveedores.create');
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
        ]);

        DemoProveedor::create([
            'demo_session_id' => session('demo_session_id'),
            'nombre' => $validated['nombre'],
        ]);

        return redirect()
            ->route('demo.proveedores.index')
            ->with(
                'success',
                'Proveedor registrado correctamente.'
            );
    }


    public function edit(DemoProveedor $proveedor): View
    {
        $this->ensureBelongsToCurrentDemo($proveedor);

        return view('demo.proveedores.edit', [
            'proveedor' => $proveedor,
        ]);
    }


    public function update(
        Request $request,
        DemoProveedor $proveedor
    ): RedirectResponse {

        $this->ensureBelongsToCurrentDemo($proveedor);

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
        ]);

        $proveedor->update($validated);

        return redirect()
            ->route('demo.proveedores.index')
            ->with(
                'success',
                'Proveedor actualizado correctamente.'
            );
    }


    public function destroy(
        DemoProveedor $proveedor
    ): RedirectResponse {

        $this->ensureBelongsToCurrentDemo($proveedor);

        $proveedor->delete();

        return redirect()
            ->route('demo.proveedores.index')
            ->with(
                'success',
                'Proveedor eliminado correctamente.'
            );
    }


    private function ensureBelongsToCurrentDemo(
        DemoProveedor $proveedor
    ): void {

        abort_unless(
            $proveedor->demo_session_id
                === session('demo_session_id'),
            404
        );
    }
}