<?php

namespace App\Http\Middleware;

use App\Models\DemoSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = session('demo_session_id');
        $demoSession = is_string($token)
            ? DemoSession::query()->where('token', $token)->first()
            : null;

        if (! $demoSession || $demoSession->expired()) {
            $token = (string) Str::uuid();
            DemoSession::query()->create([
                'token' => $token,
                'expires_at' => now()->addHours(24),
            ]);
            session(['demo_session_id' => $token]);
        }

        return $next($request);
    }
}
