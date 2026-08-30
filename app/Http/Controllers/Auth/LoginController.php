<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        if (!Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            return back()
                ->withErrors([
                    'email' => 'Correo o contraseña incorrectos.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectUser($request);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('home');
    }

    private function redirectUser(
        Request $request
    ): RedirectResponse {

        $user = $request->user();

        if ($user->is_superadmin) {
            return redirect()
                ->route('admin.dashboard');
        }

        $negocios = $user
            ->negocios()
            ->wherePivot('activo', true)
            ->where('negocios.activo', true)
            ->get();

        if ($negocios->isEmpty()) {
            return redirect()
                ->route('account.no-business');
        }

        if ($negocios->count() === 1) {
            return redirect()
                ->route(
                    'gestion.dashboard',
                    $negocios->first()
                );
        }

        return redirect()
            ->route('business.select');
    }
}