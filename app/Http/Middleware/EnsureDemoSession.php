<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('demo_session_id')) {
            session([
                'demo_session_id' => (string) Str::uuid(),
            ]);
        }

        return $next($request);
    }
}