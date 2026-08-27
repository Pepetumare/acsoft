@extends('layouts.admin')

@section('title', 'Editar usuario | ACSoft')

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
            Editar usuario
        </h1>

        <p class="text-muted mb-4">
            Modifica sus datos y accesos.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route(
                    'admin.usuarios.update',
                    $usuario
                ) }}"
                method="POST"
            >

                @csrf
                @method('PUT')

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
                        Guardar cambios
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection