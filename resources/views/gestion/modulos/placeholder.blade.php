@extends('layouts.gestion')

@section(
    'title',
    $titulo . ' | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <span class="section-eyebrow">
        Módulo
    </span>

    <h1 class="mt-2">
        {{ $titulo }}
    </h1>

    <p class="text-muted">
        Este módulo está habilitado para
        {{ $negocio->nombre }}.
    </p>

</div>

@endsection