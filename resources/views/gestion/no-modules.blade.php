@extends('layouts.gestion')

@section('title', 'Sin módulos disponibles | ' . $negocio->nombre)

@section('content')
    <div class="container-fluid p-4 p-lg-5">
        <div class="alert alert-info mb-0">
            Este negocio no tiene módulos disponibles para tu usuario.
        </div>
    </div>
@endsection
