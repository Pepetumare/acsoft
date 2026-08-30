<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BusinessSelectorController;

use App\Http\Controllers\Demo\DemoController;
use App\Http\Controllers\Demo\ProveedorController;
use App\Http\Controllers\Demo\ProductoController;
use App\Http\Controllers\Demo\IngresoController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\NegocioController;
use App\Http\Controllers\Admin\UsuarioController;

use App\Http\Controllers\Gestion\DashboardController as GestionDashboardController;
use App\Http\Controllers\Gestion\ModuloPlaceholderController;
use App\Http\Controllers\Gestion\VentaController;
use App\Http\Controllers\Gestion\GastoController;
use App\Http\Controllers\Gestion\ReporteController;
use App\Http\Controllers\Gestion\CajaController;
use App\Http\Controllers\Gestion\ProductoController as ProductoControllerGestion;
use App\Http\Controllers\Gestion\StockController;
use App\Http\Controllers\Gestion\CompraController;



/*
|--------------------------------------------------------------------------
| Página principal
|--------------------------------------------------------------------------
*/

Route::view('/', 'home.index')
    ->name('home');

Route::post('/contacto', [ContactController::class, 'store'])
    ->name('contact.store');


/*
|--------------------------------------------------------------------------
| Demo
|--------------------------------------------------------------------------
*/

Route::prefix('demo')
    ->name('demo.')
    ->middleware('demo.session')
    ->group(function () {

        Route::get('/', [DemoController::class, 'index'])
            ->name('index');

        Route::resource('proveedores', ProveedorController::class)
            ->parameters([
                'proveedores' => 'proveedor',
            ]);

        Route::resource('productos', ProductoController::class)
            ->parameters([
                'productos' => 'producto',
            ]);

        Route::resource('ingresos', IngresoController::class)
            ->only([
                'index',
                'create',
                'store',
                'destroy',
            ])
            ->parameters([
                'ingresos' => 'ingreso',
            ]);
    });


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});


Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Administración ACSoft
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'superadmin',
    ])
    ->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('clientes', ClienteController::class);

        Route::resource('negocios', NegocioController::class);

        Route::resource('usuarios', UsuarioController::class)
            ->except(['show']);
    });


/*
|--------------------------------------------------------------------------
| Cuenta
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->group(function () {

        Route::get(
            '/seleccionar-negocio',
            [BusinessSelectorController::class, 'index']
        )
            ->name('business.select');

        Route::view(
            '/cuenta/sin-negocio',
            'account.no-business'
        )
            ->name('account.no-business');

        Route::get(
            '/gestion/{negocio}',
            [GestionDashboardController::class, 'index']
        )
            ->middleware('tenant.business')
            ->name('gestion.dashboard');

        // Route::get(
        //     '/gestion/{negocio}/ventas',
        //     [ModuloPlaceholderController::class, 'ventas']
        // )
        //     ->middleware('module:ventas')
        //     ->name('gestion.ventas.index');

        Route::prefix('gestion/{negocio}/ventas')
            ->name('gestion.ventas.')
            ->middleware([
                'auth',
                'tenant.business',
                'module:ventas',
            ])
            ->group(function () {

                Route::get(
                    '/',
                    [VentaController::class, 'index']
                )->name('index');

                Route::get(
                    '/crear',
                    [VentaController::class, 'create']
                )->name('create');

                Route::post(
                    '/',
                    [VentaController::class, 'store']
                )->name('store');

                Route::delete(
                    '/{venta}',
                    [VentaController::class, 'destroy']
                )->name('destroy');
            });


        // Route::get(
        //     '/gestion/{negocio}/gastos',
        //     [ModuloPlaceholderController::class, 'gastos']
        // )
        //     ->middleware('module:gastos')
        //     ->name('gestion.gastos.index');

        Route::prefix('gestion/{negocio}/gastos')
            ->name('gestion.gastos.')
            ->middleware([
                'auth',
                'tenant.business',
                'module:gastos',
            ])
            ->group(function () {

                Route::get(
                    '/',
                    [GastoController::class, 'index']
                )->name('index');

                Route::get(
                    '/crear',
                    [GastoController::class, 'create']
                )->name('create');

                Route::post(
                    '/',
                    [GastoController::class, 'store']
                )->name('store');

                Route::delete(
                    '/{gasto}',
                    [GastoController::class, 'destroy']
                )->name('destroy');
            });


        // Route::get(
        //     '/gestion/{negocio}/reportes',
        //     [ModuloPlaceholderController::class, 'reportes']
        // )
        //     ->middleware('module:reportes')
        //     ->name('gestion.reportes.index');

        Route::get(
            '/gestion/{negocio}/reportes',
            [ReporteController::class, 'index']
        )
            ->middleware([
                'auth',
                'tenant.business',
                'module:reportes',
            ])
            ->name('gestion.reportes.index');
        Route::get(
            '/gestion/{negocio}/reportes/pdf',
            [ReporteController::class, 'pdf']
        )
            ->middleware([
                'auth',
                'tenant.business',
                'module:reportes',
            ])
            ->name('gestion.reportes.pdf');

        Route::prefix('gestion/{negocio}/caja')
            ->name('gestion.caja.')
            ->middleware([
                'auth',
                'tenant.business',
                'module:caja',
            ])
            ->group(function () {

                Route::get(
                    '/',
                    [CajaController::class, 'index']
                )->name('index');

                Route::get(
                    '/abrir',
                    [CajaController::class, 'create']
                )->name('create');

                Route::post(
                    '/abrir',
                    [CajaController::class, 'store']
                )->name('store');

                Route::post(
                    '/movimientos',
                    [CajaController::class, 'storeMovimiento']
                )->name('movimientos.store');

                Route::get(
                    '/cerrar',
                    [CajaController::class, 'close']
                )->name('close');

                Route::post(
                    '/cerrar',
                    [CajaController::class, 'destroy']
                )->name('destroy');

                Route::get(
                    '/historial',
                    [CajaController::class, 'history']
                )->name('history');
            });

        Route::prefix('gestion/{negocio}/productos')
            ->name('gestion.productos.')
            ->middleware([
                'auth',
                'tenant.business',
                'module:productos',
                'business.role:admin',
            ])
            ->group(function () {

                Route::get(
                    '/',
                    [ProductoControllerGestion::class, 'index']
                )->name('index');

                Route::get(
                    '/crear',
                    [ProductoControllerGestion::class, 'create']
                )->name('create');

                Route::post(
                    '/',
                    [ProductoControllerGestion::class, 'store']
                )->name('store');

                Route::get(
                    '/{producto}/editar',
                    [ProductoControllerGestion::class, 'edit']
                )->name('edit');

                Route::put(
                    '/{producto}',
                    [ProductoControllerGestion::class, 'update']
                )->name('update');

                Route::delete(
                    '/{producto}',
                    [ProductoControllerGestion::class, 'destroy']
                )->name('destroy');
            });

        Route::prefix('gestion/{negocio}/stock')
            ->name('gestion.stock.')
            ->middleware([
                'auth',
                'tenant.business',
                'module:stock',
                'business.role:admin',
            ])
            ->group(function () {

                Route::get(
                    '/',
                    [StockController::class, 'index']
                )->name('index');

                Route::post(
                    '/movimientos',
                    [StockController::class, 'store']
                )->name('store');

                Route::get(
                    '/historial',
                    [StockController::class, 'history']
                )->name('history');
            });

        Route::prefix('gestion/{negocio}/compras')
            ->name('gestion.compras.')
            ->middleware([
                'auth',
                'tenant.business',
                'module:compras',
                'business.role:admin',
            ])
            ->group(function () {

                Route::get(
                    '/',
                    [CompraController::class, 'index']
                )->name('index');

                Route::get(
                    '/crear',
                    [CompraController::class, 'create']
                )->name('create');

                Route::post(
                    '/',
                    [CompraController::class, 'store']
                )->name('store');

                Route::delete(
                    '/{compra}',
                    [CompraController::class, 'destroy']
                )->name('destroy');
            });
    });
