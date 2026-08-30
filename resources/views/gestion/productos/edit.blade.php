@extends('layouts.gestion')

@section(
    'title',
    'Editar producto | ' . $negocio->nombre
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
            Editar producto
        </h1>

        <p class="text-muted mb-4">
            Modifica los datos del producto.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route(
                    'gestion.productos.update',
                    [
                        $negocio,
                        $producto
                    ]
                ) }}"
                method="POST"
            >

                @csrf
                @method('PUT')

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
                        Guardar cambios
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection