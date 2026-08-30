@extends('layouts.gestion')

@section('title', $negocio->nombre . ' | ACSoft Gestión')

@section('content')

    <div class="container-fluid p-4 p-lg-5">

        <div class="gestion-page-header">

            <div>

                <span class="section-eyebrow">
                    ACSoft Gestión
                </span>

                <h1>
                    {{ $negocio->nombre }}
                </h1>

                <p>
                    Bienvenido. Estos son los módulos
                    disponibles para tu negocio.
                </p>

            </div>

        </div>


        <div class="row g-4">

            @forelse($negocio->modulosActivos as $modulo)
                <div class="col-md-6 col-xl-4">

                    <div class="gestion-module-card h-100">

                        <div class="gestion-module-icon">
                            {{ strtoupper(mb_substr($modulo->nombre, 0, 1)) }}
                        </div>

                        <div>

                            <h2>
                                {{ $modulo->nombre }}
                            </h2>

                            <p>
                                {{ $modulo->descripcion ?: 'Módulo disponible para este negocio.' }}
                            </p>


                            @if ($modulo->ruta && Route::has($modulo->ruta))
                                <a href="{{ route($modulo->ruta, $negocio) }}"
                                    class="btn btn-sm btn-acsoft-primary">
                                    Abrir
                                </a>
                            @else
                                <span class="badge text-bg-light">
                                    Próximamente
                                </span>
                            @endif

                        </div>

                    </div>

                </div>

            @empty

                <div class="col-12">

                    <div class="alert alert-info">
                        Este negocio todavía no tiene módulos habilitados.
                    </div>

                </div>
            @endforelse

        </div>

    </div>

@endsection
