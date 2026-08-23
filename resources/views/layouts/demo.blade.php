<!DOCTYPE html>
<html lang="es-CL">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Demo | ACSoft')
    </title>

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    <meta
        name="theme-color"
        content="#0F2744"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="demo-app">

    <header class="demo-topbar">

        <div class="container">

            <div class="demo-topbar-content">

                <a
                    href="{{ route('demo.index') }}"
                    class="demo-brand"
                >
                    <span>AC</span>Soft
                    <small>Demo</small>
                </a>


                <div class="demo-topbar-actions">

                    <span class="demo-mode">
                        Modo demostración
                    </span>

                    <a
                        href="{{ route('home') }}"
                        class="btn btn-sm btn-outline-light"
                    >
                        Volver a ACSoft
                    </a>

                </div>

            </div>

        </div>

    </header>


    <nav class="demo-navigation">

        <div class="container">

            <div class="demo-navigation-links">

                <a href="{{ route('demo.index') }}">
                    Inicio
                </a>

                <a href="{{ route('demo.proveedores.index') }}">
                    Proveedores
                </a>

                <a href="{{ route('demo.productos.index') }}">
                    Productos
                </a>

                <a href="{{ route('demo.ingresos.index') }}">
                    Ingresos
                </a>

            </div>

        </div>

    </nav>


    <div class="demo-warning">

        <div class="container">

            <strong>
                Estás usando una demostración.
            </strong>

            Los datos ingresados son temporales y pueden eliminarse automáticamente.

        </div>

    </div>


    <main>

        @yield('content')

    </main>


    @stack('scripts')

</body>

</html>