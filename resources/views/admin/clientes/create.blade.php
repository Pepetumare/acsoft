@extends('layouts.admin')

@section('title', 'Nuevo cliente | ACSoft')

@section('robots', 'noindex, nofollow')

@section('content')

<div class="container py-5">

    <div class="mx-auto" style="max-width: 800px;">

        <a
            href="{{ route('admin.clientes.index') }}"
            class="demo-back-link"
        >
            ← Volver a clientes
        </a>

        <span class="section-eyebrow">
            Administración
        </span>

        <h1 class="mt-2 mb-4">
            Nuevo cliente
        </h1>

        <div class="contact-form-card">

            <form
                action="{{ route('admin.clientes.store') }}"
                method="POST"
            >

                @csrf

                @include('admin.clientes._form')

                <div class="demo-form-actions">

                    <a
                        href="{{ route('admin.clientes.index') }}"
                        class="btn btn-demo-cancel"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Crear cliente
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection