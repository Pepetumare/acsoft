@extends('layouts.app')

@section(
    'title',
    'Demo del sistema | ACSoft'
)

@section(
    'description',
    'Prueba una demostración del sistema ACSoft para recepción y control de mercadería.'
)

@section(
    'robots',
    'noindex, follow'
)

@section('content')

    <div class="container py-5">

        <h1>
            Demo ACSoft
        </h1>

        <p>
            Próximamente integraremos aquí el sistema de demostración.
        </p>

        <a href="{{ route('home') }}" class="btn btn-primary">
            Volver
        </a>

    </div>

@endsection