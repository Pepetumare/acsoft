@extends('layouts.gestion')

@section('title', 'Nuevo usuario | ' . $negocio->nombre)

@section('content')
<div class="container-fluid p-4 p-lg-5">
    <div class="mb-4">
        <span class="section-eyebrow">Administración</span>
        <h1 class="mt-2 mb-1">Nuevo usuario</h1>
        <p class="text-muted mb-0">Crea una cuenta nueva o envía una invitación a una cuenta existente.</p>
    </div>
    <div class="contact-form-card">
        <form method="POST" action="{{ route('gestion.usuarios.store', $negocio) }}">
            @csrf
            @include('gestion.usuarios._form')
            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-acsoft-primary">Guardar usuario</button>
                <a href="{{ route('gestion.usuarios.index', $negocio) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
