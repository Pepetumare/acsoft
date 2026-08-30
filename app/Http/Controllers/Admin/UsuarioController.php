<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::with([
            'negocios' => function ($query) {
                $query->orderBy('nombre');
            },
        ])
            ->where('is_superadmin', false)
            ->orderBy('name')
            ->paginate(15);

        return view(
            'admin.usuarios.index',
            compact('usuarios')
        );
    }


    public function create(): View
    {
        $negocios = Negocio::with('cliente')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.usuarios.create',
            compact('negocios')
        );
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'negocios' => [
                'nullable',
                'array',
            ],

            'negocios.*' => [
                'integer',
                Rule::exists('negocios', 'id')
                    ->where('activo', true),
            ],

            'rol' => [
                'required',
                Rule::in([
                    'admin',
                    'usuario',
                ]),
            ],
        ]);


        $usuario = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(
                $validated['password']
            ),
            'is_superadmin' => false,
        ]);


        $this->syncNegocios(
            $usuario,
            $validated['negocios'] ?? [],
            $validated['rol']
        );


        return redirect()
            ->route('admin.usuarios.index')
            ->with(
                'success',
                'Usuario creado correctamente.'
            );
    }


    public function edit(User $usuario): View
    {
        abort_if(
            $usuario->is_superadmin,
            403
        );

        $usuario->load('negocios');

        $negocios = Negocio::with('cliente')
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.usuarios.edit',
            compact(
                'usuario',
                'negocios'
            )
        );
    }


    public function update(
        Request $request,
        User $usuario
    ): RedirectResponse {

        abort_if(
            $usuario->is_superadmin,
            403
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->ignore($usuario->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'negocios' => [
                'nullable',
                'array',
            ],

            'negocios.*' => [
                'integer',
                Rule::exists('negocios', 'id'),
            ],

            'rol' => [
                'required',
                Rule::in([
                    'admin',
                    'usuario',
                ]),
            ],
        ]);


        $usuario->name = $validated['name'];
        $usuario->email = $validated['email'];

        if (!empty($validated['password'])) {
            $usuario->password = Hash::make(
                $validated['password']
            );
        }

        $usuario->save();


        $this->syncNegocios(
            $usuario,
            $validated['negocios'] ?? [],
            $validated['rol']
        );


        return redirect()
            ->route('admin.usuarios.index')
            ->with(
                'success',
                'Usuario actualizado correctamente.'
            );
    }


    public function destroy(
        User $usuario
    ): RedirectResponse {

        abort_if(
            $usuario->is_superadmin,
            403
        );

        $usuario->delete();

        return redirect()
            ->route('admin.usuarios.index')
            ->with(
                'success',
                'Usuario eliminado correctamente.'
            );
    }


    private function syncNegocios(
        User $usuario,
        array $negocios,
        string $rol
    ): void {

        $sync = [];

        foreach ($negocios as $negocioId) {
            $sync[$negocioId] = [
                'rol' => $rol,
                'activo' => true,
            ];
        }

        $usuario
            ->negocios()
            ->sync($sync);
    }
}
