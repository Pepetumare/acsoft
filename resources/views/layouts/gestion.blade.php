<!DOCTYPE html>
<html lang="es-CL">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'ACSoft Gestión')
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

<body class="gestion-app">

    <header class="gestion-topbar">

        <div class="container-fluid">

            <div class="gestion-topbar-inner">

                <a
                    href="{{ route(
                        'gestion.dashboard',
                        $negocio
                    ) }}"
                    class="gestion-brand"
                >
                    <span>AC</span>Soft
                    <small>Gestión</small>
                </a>


                <div class="gestion-topbar-user">

                    <div class="d-none d-md-block text-end">

                        <strong>
                            {{ auth()->user()->name }}
                        </strong>

                        <span>
                            {{ $negocio->nombre }}
                        </span>

                    </div>


                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-sm btn-outline-light"
                        >
                            Salir
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </header>


    <div class="gestion-shell">

        <aside class="gestion-sidebar">

            <nav>

                <a
                    href="{{ route(
                        'gestion.dashboard',
                        $negocio
                    ) }}"
                    class="gestion-nav-link
                        {{ request()->routeIs('gestion.dashboard')
                            ? 'active'
                            : '' }}"
                >
                    Dashboard
                </a>


                @foreach($negocio->modulosActivos as $modulo)

                    @if(
                        $modulo->ruta
                        && Route::has($modulo->ruta)
                    )

                        <a
                            href="{{ route(
                                $modulo->ruta,
                                $negocio
                            ) }}"
                            class="gestion-nav-link
                                {{ request()->routeIs(
                                    $modulo->ruta
                                )
                                    ? 'active'
                                    : '' }}"
                        >
                            {{ $modulo->nombre }}
                        </a>

                    @else

                        <span
                            class="gestion-nav-link gestion-nav-disabled"
                        >
                            {{ $modulo->nombre }}

                            <small>
                                próximamente
                            </small>
                        </span>

                    @endif

                @endforeach

            </nav>

        </aside>


        <main class="gestion-main">

            @yield('content')

        </main>

    </div>

    @stack('scripts')

</body>

</html>