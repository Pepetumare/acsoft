<?php

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Negocio|null $negocio */
        $negocio = $request->route('negocio');
        $user = $request->user();

        abort_unless($negocio instanceof Negocio, 404);
        abort_unless($negocio->activo, 403);
        abort_unless($user, 403);

        if (!$user->is_superadmin) {
            $tieneAcceso = $user
                ->negocios()
                ->where('negocios.id', $negocio->id)
                ->wherePivot('activo', true)
                ->exists();

            abort_unless($tieneAcceso, 403);
        }

        return $next($request);
    }
}
