@extends('layouts.admin')

@section('title', 'Nuevo usuario | ACSoft')

@section('robots', 'noindex, nofollow')

@section('content')

<div class="container py-5">

    <div class="mx-auto" style="max-width: 980px;">

        <a
            href="{{ route('admin.usuarios.index') }}"
            class="demo-back-link"
        >
            ← Volver a usuarios
        </a>

        <span class="section-eyebrow">
            Administración
        </span>

        <h1 class="mt-2">
            Nuevo usuario
        </h1>

        <p class="text-muted mb-4">
            Crea las credenciales y selecciona
            a qué negocio tendrá acceso.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route('admin.usuarios.store') }}"
                method="POST"
            >

                @csrf

                @include('admin.usuarios._form')


                <div class="demo-form-actions">

                    <a
                        href="{{ route('admin.usuarios.index') }}"
                        class="btn btn-demo-cancel"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Crear usuario
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection