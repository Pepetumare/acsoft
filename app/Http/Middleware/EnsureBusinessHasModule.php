<?php

namespace App\Http\Middleware;

use App\Models\Negocio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessHasModule
{
    public function handle(
        Request $request,
        Closure $next,
        string $modulo
    ): Response {

        /** @var Negocio|null $negocio */
        $negocio = $request->route('negocio');

        abort_unless(
            $negocio instanceof Negocio,
            404
        );

        abort_unless(
            $negocio->tieneModulo($modulo),
            403,
            'Este módulo no está habilitado para este negocio.'
        );

        return $next($request);
    }
}
