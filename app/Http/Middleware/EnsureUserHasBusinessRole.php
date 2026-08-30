<?php

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasBusinessRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var Negocio|null $negocio */
        $negocio = $request->route('negocio');
        $user = $request->user();

        abort_unless($negocio instanceof Negocio, 404);
        abort_unless($user, 403);

        if (!$user->is_superadmin) {
            $tieneRol = $user
                ->negocios()
                ->where('negocios.id', $negocio->id)
                ->wherePivot('activo', true)
                ->wherePivotIn('rol', $roles)
                ->exists();

            abort_unless($tieneRol, 403);
        }

        return $next($request);
    }
}
