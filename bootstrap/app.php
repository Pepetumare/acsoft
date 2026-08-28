<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureDemoSession;
use App\Http\Middleware\EnsureUserIsSuperadmin;
use App\Http\Middleware\EnsureBusinessHasModule;
use App\Http\Middleware\EnsureUserCanAccessBusiness;
use App\Http\Middleware\EnsureUserHasBusinessRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'demo.session' => EnsureDemoSession::class,
            'superadmin' => EnsureUserIsSuperadmin::class,
            'module' => EnsureBusinessHasModule::class,
            'tenant.business' => EnsureUserCanAccessBusiness::class,
            'business.role' => EnsureUserHasBusinessRole::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
