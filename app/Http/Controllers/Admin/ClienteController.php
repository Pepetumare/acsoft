<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): View
    {
        $clientes = Cliente::withCount('negocios')
            ->latest()
            ->paginate(15);

        return view(
            'admin.clientes.index',
            compact('clientes')
        );
    }

    public function create(): View
    {
        return view('admin.clientes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:50',
            ],

            'notas' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        Cliente::create([
            ...$validated,
            'activo' => true,
        ]);

        return redirect()
            ->route('admin.clientes.index')
            ->with(
                'success',
                'Cliente creado correctamente.'
            );
    }

    public function edit(Cliente $cliente): View
    {
        return view(
            'admin.clientes.edit',
            compact('cliente')
        );
    }

    public function update(
        Request $request,
        Cliente $cliente
    ): RedirectResponse {

        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:50',
            ],

            'notas' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],
        ]);

        $cliente->update([
            ...$validated,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('admin.clientes.index')
            ->with(
                'success',
                'Cliente actualizado correctamente.'
            );
    }

    public function destroy(
        Cliente $cliente
    ): RedirectResponse {

        if ($cliente->negocios()->exists()) {
            return back()->with(
                'error',
                'No puedes eliminar un cliente que tiene negocios asociados.'
            );
        }

        $cliente->delete();

        return redirect()
            ->route('admin.clientes.index')
            ->with(
                'success',
                'Cliente eliminado correctamente.'
            );
    }
}