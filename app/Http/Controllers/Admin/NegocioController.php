<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NegocioController extends Controller
{
    public function index(): View
    {
        $negocios = Negocio::with([
            'cliente',
            'modulos' => function ($query) {
                $query
                    ->wherePivot('activo', true)
                    ->orderBy('orden');
            },
        ])
        ->latest()
        ->paginate(15);

        return view(
            'admin.negocios.index',
            compact('negocios')
        );
    }


    public function create(): View
    {
        $clientes = Cliente::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $modulos = Modulo::where('activo', true)
            ->orderBy('categoria')
            ->orderBy('orden')
            ->get()
            ->groupBy('categoria');

        return view(
            'admin.negocios.create',
            compact(
                'clientes',
                'modulos'
            )
        );
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => [
                'required',
                'integer',
                Rule::exists('clientes', 'id')
                    ->where('activo', true),
            ],

            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:150',
                'alpha_dash',
                'unique:negocios,slug',
            ],

            'subdominio' => [
                'nullable',
                'string',
                'max:100',
                'alpha_dash',
                'unique:negocios,subdominio',
            ],

            'modulos' => [
                'nullable',
                'array',
            ],

            'modulos.*' => [
                'integer',
                Rule::exists('modulos', 'id')
                    ->where('activo', true),
            ],
        ]);


        $slug = $validated['slug']
            ?? $this->generateUniqueSlug(
                $validated['nombre']
            );


        $negocio = Negocio::create([
            'cliente_id' => $validated['cliente_id'],
            'nombre' => $validated['nombre'],
            'slug' => $slug,
            'subdominio' => $validated['subdominio'] ?? null,
            'activo' => true,
        ]);


        $this->syncModulos(
            $negocio,
            $validated['modulos'] ?? []
        );


        return redirect()
            ->route('admin.negocios.index')
            ->with(
                'success',
                'Negocio creado correctamente.'
            );
    }


    public function edit(Negocio $negocio): View
    {
        $negocio->load('modulos');

        $clientes = Cliente::orderBy('nombre')
            ->get();

        $modulos = Modulo::where('activo', true)
            ->orderBy('categoria')
            ->orderBy('orden')
            ->get()
            ->groupBy('categoria');

        return view(
            'admin.negocios.edit',
            compact(
                'negocio',
                'clientes',
                'modulos'
            )
        );
    }


    public function update(
        Request $request,
        Negocio $negocio
    ): RedirectResponse {

        $validated = $request->validate([
            'cliente_id' => [
                'required',
                'integer',
                Rule::exists('clientes', 'id'),
            ],

            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'required',
                'string',
                'max:150',
                'alpha_dash',
                Rule::unique('negocios', 'slug')
                    ->ignore($negocio->id),
            ],

            'subdominio' => [
                'nullable',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('negocios', 'subdominio')
                    ->ignore($negocio->id),
            ],

            'modulos' => [
                'nullable',
                'array',
            ],

            'modulos.*' => [
                'integer',
                Rule::exists('modulos', 'id')
                    ->where('activo', true),
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],
        ]);


        $negocio->update([
            'cliente_id' => $validated['cliente_id'],
            'nombre' => $validated['nombre'],
            'slug' => $validated['slug'],
            'subdominio' => $validated['subdominio'] ?? null,
            'activo' => $request->boolean('activo'),
        ]);


        $this->syncModulos(
            $negocio,
            $validated['modulos'] ?? []
        );


        return redirect()
            ->route('admin.negocios.index')
            ->with(
                'success',
                'Negocio actualizado correctamente.'
            );
    }


    public function destroy(
        Negocio $negocio
    ): RedirectResponse {

        if ($negocio->usuarios()->exists()) {
            return back()->with(
                'error',
                'No puedes eliminar un negocio que tiene usuarios asociados.'
            );
        }

        $negocio->delete();

        return redirect()
            ->route('admin.negocios.index')
            ->with(
                'success',
                'Negocio eliminado correctamente.'
            );
    }


    private function generateUniqueSlug(
        string $nombre
    ): string {

        $base = Str::slug($nombre);

        $slug = $base;

        $counter = 2;

        while (
            Negocio::where('slug', $slug)->exists()
        ) {

            $slug = $base . '-' . $counter;

            $counter++;
        }

        return $slug;
    }


    private function syncModulos(
        Negocio $negocio,
        array $modulos
    ): void {

        $syncData = [];

        foreach ($modulos as $moduloId) {
            $syncData[$moduloId] = [
                'activo' => true,
            ];
        }

        $negocio
            ->modulos()
            ->sync($syncData);
    }
}