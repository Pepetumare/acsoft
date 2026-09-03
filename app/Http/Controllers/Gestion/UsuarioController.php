<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Mail\NegocioInvitacionMail;
use App\Models\Negocio;
use App\Models\NegocioInvitacion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $usuarios = $negocio->usuarios()
            ->orderBy('name')
            ->paginate(15);

        return view('gestion.usuarios.index', compact('negocio', 'usuarios'));
    }

    public function create(Negocio $negocio): View
    {
        return view('gestion.usuarios.create', compact('negocio'));
    }

    public function store(Request $request, Negocio $negocio): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', Rule::in(['admin', 'usuario'])],
        ]);

        $email = mb_strtolower(trim($validated['email']));

        $plainToken = DB::transaction(function () use ($negocio, $validated, $email, $request): ?string {
            $usuario = User::where('email', $email)->lockForUpdate()->first();

            if ($usuario && $usuario->is_superadmin) {
                throw ValidationException::withMessages([
                    'email' => 'Este correo corresponde a un superadministrador y no puede asociarse al negocio.',
                ]);
            }

            if ($usuario && $negocio->usuarios()->where('users.id', $usuario->id)->exists()) {
                return null;
            }

            if (!$usuario) {
                $usuario = User::create([
                    'name' => $validated['name'],
                    'email' => $email,
                    'password' => Hash::make($validated['password']),
                    'is_superadmin' => false,
                ]);
                $negocio->usuarios()->attach($usuario->id, [
                    'rol' => $validated['rol'],
                    'activo' => true,
                ]);

                return null;
            }

            $plainToken = Str::random(64);
            NegocioInvitacion::query()->updateOrCreate(
                ['negocio_id' => $negocio->id, 'email' => $email],
                [
                    'rol' => $validated['rol'],
                    'token_hash' => hash('sha256', $plainToken),
                    'expires_at' => now()->addHours(72),
                    'accepted_at' => null,
                    'created_by' => $request->user()->id,
                ]
            );

            return $plainToken;
        });

        $redirect = redirect()->route('gestion.usuarios.index', $negocio)
            ->with('success', 'El usuario fue creado o invitado correctamente.');

        if ($plainToken) {
            $invitationUrl = route('business-invitations.show', $plainToken);

            if (app()->environment(['local', 'development'])) {
                $redirect->with('invitation_url', $invitationUrl);
            } elseif (config('mail.default') !== 'log') {
                Mail::to($email)->send(new NegocioInvitacionMail($negocio, $invitationUrl));
            }
        }

        return $redirect;
    }

    public function edit(Negocio $negocio, User $usuario): View
    {
        $this->ensureBelongsToBusiness($negocio, $usuario);
        $usuario->load(['negocios' => fn ($query) => $query->where('negocios.id', $negocio->id)]);

        return view('gestion.usuarios.edit', compact('negocio', 'usuario'));
    }

    public function update(Request $request, Negocio $negocio, User $usuario): RedirectResponse
    {
        $this->ensureBelongsToBusiness($negocio, $usuario);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'rol' => ['required', Rule::in(['admin', 'usuario'])],
            'activo' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($negocio, $usuario, $validated): void {
            $pivot = DB::table('negocio_user')
                ->where('negocio_id', $negocio->id)
                ->where('user_id', $usuario->id)
                ->lockForUpdate()
                ->first();

            abort_unless($pivot, 403);

            if ($pivot->rol === 'admin' && $pivot->activo
                && ($validated['rol'] !== 'admin' || !$validated['activo'])) {
                $this->ensureAnotherActiveAdmin($negocio, $usuario);
            }

            $usuario->update(['name' => $validated['name']]);
            $negocio->usuarios()->updateExistingPivot($usuario->id, [
                'rol' => $validated['rol'],
                'activo' => $validated['activo'],
            ]);
        });

        return redirect()->route('gestion.usuarios.index', $negocio)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Negocio $negocio, User $usuario): RedirectResponse
    {
        $this->ensureBelongsToBusiness($negocio, $usuario);

        DB::transaction(function () use ($negocio, $usuario): void {
            $pivot = DB::table('negocio_user')
                ->where('negocio_id', $negocio->id)
                ->where('user_id', $usuario->id)
                ->lockForUpdate()
                ->first();

            abort_unless($pivot, 403);

            if ($pivot->rol === 'admin' && $pivot->activo) {
                $this->ensureAnotherActiveAdmin($negocio, $usuario);
            }

            $negocio->usuarios()->detach($usuario->id);
        });

        return redirect()->route('gestion.usuarios.index', $negocio)
            ->with('success', 'Usuario quitado del negocio correctamente.');
    }

    private function ensureBelongsToBusiness(Negocio $negocio, User $usuario): void
    {
        abort_unless(
            $negocio->usuarios()->where('users.id', $usuario->id)->exists(),
            403
        );
        abort_if($usuario->is_superadmin, 403);
    }

    private function ensureAnotherActiveAdmin(Negocio $negocio, User $usuario): void
    {
        $hasAnotherAdmin = DB::table('negocio_user')
            ->where('negocio_id', $negocio->id)
            ->where('user_id', '!=', $usuario->id)
            ->where('rol', 'admin')
            ->where('activo', true)
            ->lockForUpdate()
            ->exists();

        if (!$hasAnotherAdmin) {
            throw ValidationException::withMessages([
                'rol' => 'El negocio debe conservar al menos un administrador activo.',
            ]);
        }
    }
}
