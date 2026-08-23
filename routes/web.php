<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\Demo\DemoController;
use App\Http\Controllers\Demo\IngresoController;
use App\Http\Controllers\Demo\ProductoController;
use App\Http\Controllers\Demo\ProveedorController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home.index')
    ->name('home');

Route::prefix('demo')
    ->name('demo.')
    ->middleware('demo.session')
    ->group(function () {

        Route::get('/', [DemoController::class, 'index'])
            ->name('index');

        Route::resource(
            'proveedores',
            ProveedorController::class
        );

        Route::resource(
            'productos',
            ProductoController::class
        );

        Route::resource(
            'ingresos',
            IngresoController::class
        );
    });

Route::post('/contacto', [ContactController::class, 'store'])
    ->middleware('throttle:5,15')
    ->name('contact.store');

Route::get('/sitemap.xml', function () {

    $urls = [
        [
            'loc' => route('home'),
            'priority' => '1.0',
        ],
    ];

    return response()
        ->view('seo.sitemap', compact('urls'))
        ->header('Content-Type', 'application/xml');
})
    ->name('sitemap');
