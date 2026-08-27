@extends('layouts.gestion')

@section(
    'title',
    'Nuevo producto | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div style="max-width: 900px;">

        <a
            href="{{ route(
                'gestion.productos.index',
                $negocio
            ) }}"
            class="demo-back-link"
        >
            ← Volver a productos
        </a>

        <span class="section-eyebrow">
            Inventario
        </span>

        <h1 class="mt-2">
            Nuevo producto
        </h1>

        <p class="text-muted mb-4">
            Agrega un producto al catálogo del negocio.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route(
                    'gestion.productos.store',
                    $negocio
                ) }}"
                method="POST"
            >

                @csrf

                @include(
                    'gestion.productos._form'
                )


                <div class="demo-form-actions">

                    <a
                        href="{{ route(
                            'gestion.productos.index',
                            $negocio
                        ) }}"
                        class="btn btn-demo-cancel"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Crear producto
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection