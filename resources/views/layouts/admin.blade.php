<!DOCTYPE html>
<html lang="es-CL">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'ACSoft Administración')
    </title>

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="admin-app">

    <header class="gestion-topbar">

        <div class="container-fluid">

            <div class="gestion-topbar-inner">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="gestion-brand"
                >
                    <span>AC</span>Soft
                    <small>Admin</small>
                </a>


                <div class="d-flex align-items-center gap-2">

                    <a
                        href="{{ route('home') }}"
                        class="btn btn-sm btn-outline-light"
                    >
                        Ir al inicio
                    </a>

                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-sm btn-outline-light"
                        >
                            Cerrar sesión
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
                    href="{{ route('admin.dashboard') }}"
                    class="gestion-nav-link
                        {{ request()->routeIs('admin.dashboard')
                            ? 'active'
                            : '' }}"
                >
                    Inicio
                </a>

                <a
                    href="{{ route('admin.clientes.index') }}"
                    class="gestion-nav-link
                        {{ request()->routeIs('admin.clientes.*')
                            ? 'active'
                            : '' }}"
                >
                    Clientes
                </a>

                <a
                    href="{{ route('admin.negocios.index') }}"
                    class="gestion-nav-link
                        {{ request()->routeIs('admin.negocios.*')
                            ? 'active'
                            : '' }}"
                >
                    Negocios
                </a>

                <a
                    href="{{ route('admin.usuarios.index') }}"
                    class="gestion-nav-link
                        {{ request()->routeIs('admin.usuarios.*')
                            ? 'active'
                            : '' }}"
                >
                    Usuarios
                </a>

                <a href="{{ route('admin.solicitudes.index') }}" class="gestion-nav-link {{ request()->routeIs('admin.solicitudes.*') ? 'active' : '' }}">
                    Solicitudes
                </a>

            </nav>

        </aside>


        <main class="gestion-main">

            @yield('content')

        </main>

    </div>

</body>

</html>
