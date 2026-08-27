@extends('layouts.admin')

@section('title', 'Nuevo negocio | ACSoft')

@section('robots', 'noindex, nofollow')

@section('content')

<div class="container py-5">

    <div class="mx-auto" style="max-width: 980px;">

        <a
            href="{{ route('admin.negocios.index') }}"
            class="demo-back-link"
        >
            ← Volver a negocios
        </a>


        <span class="section-eyebrow">
            Administración
        </span>

        <h1 class="mt-2">
            Nuevo negocio
        </h1>

        <p class="text-muted mb-4">
            Crea un negocio y selecciona
            qué módulos tendrá disponibles.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route('admin.negocios.store') }}"
                method="POST"
            >

                @csrf

                @include('admin.negocios._form')


                <div class="demo-form-actions">

                    <a
                        href="{{ route('admin.negocios.index') }}"
                        class="btn btn-demo-cancel"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Crear negocio
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection