@extends('layouts.app')

@section('title', 'Seleccionar negocio | ACSoft')

@section('robots', 'noindex, nofollow')

@section('content')

<div class="container py-5">

    <div class="text-center mb-5">

        <span class="section-eyebrow">
            ACSoft Gestión
        </span>

        <h1 class="mt-2">
            Selecciona un negocio
        </h1>

        <p class="text-muted">
            Tu cuenta tiene acceso a más de un negocio.
        </p>

    </div>


    <div class="row justify-content-center g-3">

        @foreach($negocios as $negocio)

            <div class="col-md-6 col-lg-4">

                <div class="card h-100 border">

                    <div class="card-body">

                        <h2 class="h5">
                            {{ $negocio->nombre }}
                        </h2>

                        <a
                            href="{{ route(
                                'gestion.dashboard',
                                $negocio
                            ) }}"
                            class="btn btn-acsoft-primary mt-3"
                        >
                            Entrar
                        </a>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>

@endsection
