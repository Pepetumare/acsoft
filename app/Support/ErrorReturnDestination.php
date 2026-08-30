<?php

namespace App\Support;

use App\Models\Negocio;
use Illuminate\Http\Request;

class ErrorReturnDestination
{
    /**
     * @return array{url: string, label: string, secondary_url: ?string, secondary_label: ?string}
     */
    public function resolve(Request $request, int $status): array
    {
        $user = $request->user();

        if (!$user) {
            $useLogin = in_array($status, [401, 419], true);

            return [
                'url' => $useLogin ? route('login') : route('home'),
                'label' => $useLogin ? 'Iniciar sesión nuevamente' : 'Ir al inicio',
                'secondary_url' => $useLogin ? route('home') : route('login'),
                'secondary_label' => $useLogin ? 'Ir al inicio' : 'Iniciar sesión',
            ];
        }

        if ($user->is_superadmin) {
            return $this->authenticatedDestination(
                route('admin.dashboard'),
                'Volver a administración'
            );
        }

        $routeBusiness = $request->route('negocio');
        $routeBusiness = $routeBusiness instanceof Negocio
            ? $routeBusiness
            : Negocio::where('slug', (string) $routeBusiness)->first();

        if ($routeBusiness && $routeBusiness->activo
            && $user->negocios()
                ->where('negocios.id', $routeBusiness->id)
                ->wherePivot('activo', true)
                ->exists()) {
            return $this->authenticatedDestination(
                route('gestion.dashboard', $routeBusiness),
                'Volver al dashboard'
            );
        }

        $businesses = $user->negocios()
            ->where('negocios.activo', true)
            ->wherePivot('activo', true)
            ->limit(2)
            ->get();

        if ($businesses->count() === 1) {
            return $this->authenticatedDestination(
                route('gestion.dashboard', $businesses->first()),
                'Volver al dashboard'
            );
        }

        if ($businesses->count() > 1) {
            return $this->authenticatedDestination(
                route('business.select'),
                'Seleccionar negocio'
            );
        }

        return $this->authenticatedDestination(
            route('account.no-business'),
            'Volver a mi cuenta'
        );
    }

    /**
     * @return array{url: string, label: string, secondary_url: string, secondary_label: string}
     */
    private function authenticatedDestination(string $url, string $label): array
    {
        return [
            'url' => $url,
            'label' => $label,
            'secondary_url' => route('home'),
            'secondary_label' => 'Ir al inicio',
        ];
    }
}
