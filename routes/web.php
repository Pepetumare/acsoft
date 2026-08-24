<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\Demo\DemoController;
use App\Http\Controllers\Demo\ProveedorController;
use App\Http\Controllers\Demo\ProductoController;
use App\Http\Controllers\Demo\IngresoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home.index')
    ->name('home');

Route::post('/contacto', [ContactController::class, 'store'])
    ->name('contact.store');


Route::prefix('demo')
    ->name('demo.')
    ->middleware('demo.session')
    ->group(function () {

        Route::get('/', [DemoController::class, 'index'])
            ->name('index');

        Route::resource(
            'proveedores',
            ProveedorController::class
        )->parameters([
            'proveedores' => 'proveedor',
        ]);

        Route::resource(
            'productos',
            ProductoController::class
        );

        Route::resource(
            'ingresos',
            IngresoController::class
        );

    });