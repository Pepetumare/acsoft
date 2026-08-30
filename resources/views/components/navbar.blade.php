<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container py-2">

        {{-- <a class="navbar-brand fw-bold fs-4" href="{{ route('home') }}">
            <span class="text-acsoft-primary">AC</span><span class="text-acsoft-accent">Soft</span>
        </a> --}}

        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}" aria-label="ACSoft - Inicio">
            <img src="{{ asset('images/acsoft/logo.svg') }}" alt="ACSoft" class="acsoft-logo">
        </a>


        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarACSoft"
            aria-controls="navbarACSoft" aria-expanded="false" aria-label="Abrir menú principal">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarACSoft">

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('functions') ? 'active' : '' }}" href="{{ route('functions') }}">Funciones</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">Precios</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contacto') ? 'active' : '' }}" href="{{ route('contacto') }}">Contacto</a></li>

                <div class="d-flex gap-2">

                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            Iniciar sesión
                        </a>
                    @else
                        <a href="{{ auth()->user()->dashboardUrl() }}" class="btn btn-acsoft-primary">
                            Dashboard
                        </a>
                    @endguest

                </div>

            </ul>

        </div>
    </div>
</nav>
