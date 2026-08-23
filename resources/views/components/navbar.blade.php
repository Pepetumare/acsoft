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

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#solucion">
                        Solución
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#como-funciona">
                        Cómo funciona
                    </a>
                </li>
                <a class="nav-link" href="{{ route('home') }}#caracteristicas">
                    Características
                </a>

                <a class="nav-link" href="{{ route('home') }}#servicios">
                    Servicios
                </a>

                <a class="nav-link" href="{{ route('home') }}#contacto">
                    Contacto
                </a>

                <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                    <a href="{{ route('demo.index') }}" class="btn btn-acsoft-primary px-4">
                        Probar demo
                    </a>
                </li>

            </ul>

        </div>
    </div>
</nav>
