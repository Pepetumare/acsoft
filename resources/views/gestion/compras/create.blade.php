@extends('layouts.gestion')

@section(
    'title',
    'Nueva compra | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div style="max-width: 1050px;">

        <a
            href="{{ route(
                'gestion.compras.index',
                $negocio
            ) }}"
            class="demo-back-link"
        >
            ← Volver a compras
        </a>


        <span class="section-eyebrow">
            Inventario
        </span>

        <h1 class="mt-2">
            Nueva compra
        </h1>

        <p class="text-muted mb-4">
            Registra productos adquiridos para el negocio.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route(
                    'gestion.compras.store',
                    $negocio
                ) }}"
                method="POST"
            >

                @csrf


                <div class="row g-3 mb-4">

                    <div class="col-md-4">

                        <label
                            for="fecha"
                            class="form-label"
                        >
                            Fecha *
                        </label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            value="{{ old(
                                'fecha',
                                now()->toDateString()
                            ) }}"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-8">

                        <label
                            for="proveedor"
                            class="form-label"
                        >
                            Proveedor
                        </label>

                        <input
                            type="text"
                            id="proveedor"
                            name="proveedor"
                            value="{{ old('proveedor') }}"
                            class="form-control"
                            placeholder="Ej: Distribuidora Central"
                        >

                    </div>

                </div>


                <h2 class="h5 mb-3">
                    Productos
                </h2>


                <div id="compra-detalles">

                    <div
                        class="compra-detalle-row
                        row g-2 align-items-end mb-3"
                    >

                        <div class="col-md-5">

                            <label class="form-label">
                                Producto
                            </label>

                            <select
                                name="detalles[0][producto_id]"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecciona
                                </option>

                                @foreach($productos as $producto)

                                    <option
                                        value="{{ $producto->id }}"
                                    >
                                        {{ $producto->nombre }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                Cantidad
                            </label>

                            <input
                                type="number"
                                name="detalles[0][cantidad]"
                                class="form-control compra-cantidad"
                                min="0.001"
                                step="0.001"
                                value="1"
                                required
                            >

                        </div>


                        <div class="col-md-3">

                            <label class="form-label">
                                Costo unitario
                            </label>

                            <input
                                type="number"
                                name="detalles[0][costo_unitario]"
                                class="form-control compra-costo"
                                min="0"
                                step="1"
                                required
                            >

                        </div>


                        <div class="col-md-2">

                            <button
                                type="button"
                                class="btn btn-outline-danger
                                    w-100 quitar-detalle"
                            >
                                Quitar
                            </button>

                        </div>

                    </div>

                </div>


                <button
                    type="button"
                    id="agregar-detalle"
                    class="btn btn-outline-secondary mb-4"
                >
                    + Agregar producto
                </button>


                <div
                    class="d-flex
                    justify-content-between
                    align-items-center
                    border-top pt-4"
                >

                    <strong>
                        Total estimado
                    </strong>

                    <strong
                        id="compra-total"
                        class="fs-4"
                    >
                        $0
                    </strong>

                </div>


                <div class="mt-4">

                    <label
                        for="observacion"
                        class="form-label"
                    >
                        Observación
                    </label>

                    <textarea
                        id="observacion"
                        name="observacion"
                        rows="3"
                        class="form-control"
                    >{{ old('observacion') }}</textarea>

                </div>


                <div class="demo-form-actions">

                    <a
                        href="{{ route(
                            'gestion.compras.index',
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
                        Registrar compra
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', () => {

    const contenedor =
        document.getElementById('compra-detalles');

    const agregar =
        document.getElementById('agregar-detalle');

    const total =
        document.getElementById('compra-total');

    let indice = 1;


    const opcionesProductos = `
        @foreach($productos as $producto)
            <option value="{{ $producto->id }}">
                {{ addslashes($producto->nombre) }}
            </option>
        @endforeach
    `;


    function calcularTotal() {

        let valor = 0;

        contenedor
            .querySelectorAll('.compra-detalle-row')
            .forEach(fila => {

                const cantidad =
                    parseFloat(
                        fila.querySelector(
                            '.compra-cantidad'
                        ).value
                    ) || 0;

                const costo =
                    parseFloat(
                        fila.querySelector(
                            '.compra-costo'
                        ).value
                    ) || 0;

                valor += cantidad * costo;

            });


        total.textContent =
            '$' + Math.round(valor)
                .toLocaleString('es-CL');
    }


    agregar.addEventListener('click', () => {

        const fila =
            document.createElement('div');

        fila.className =
            'compra-detalle-row row g-2 align-items-end mb-3';

        fila.innerHTML = `

            <div class="col-md-5">

                <label class="form-label">
                    Producto
                </label>

                <select
                    name="detalles[${indice}][producto_id]"
                    class="form-select"
                    required
                >

                    <option value="">
                        Selecciona
                    </option>

                    ${opcionesProductos}

                </select>

            </div>

            <div class="col-md-2">

                <label class="form-label">
                    Cantidad
                </label>

                <input
                    type="number"
                    name="detalles[${indice}][cantidad]"
                    class="form-control compra-cantidad"
                    min="0.001"
                    step="0.001"
                    value="1"
                    required
                >

            </div>

            <div class="col-md-3">

                <label class="form-label">
                    Costo unitario
                </label>

                <input
                    type="number"
                    name="detalles[${indice}][costo_unitario]"
                    class="form-control compra-costo"
                    min="0"
                    step="1"
                    required
                >

            </div>

            <div class="col-md-2">

                <button
                    type="button"
                    class="btn btn-outline-danger
                        w-100 quitar-detalle"
                >
                    Quitar
                </button>

            </div>
        `;

        contenedor.appendChild(fila);

        indice++;
    });


    contenedor.addEventListener(
        'input',
        calcularTotal
    );


    contenedor.addEventListener(
        'click',
        event => {

            if (
                !event.target
                    .classList
                    .contains('quitar-detalle')
            ) {
                return;
            }

            const filas =
                contenedor.querySelectorAll(
                    '.compra-detalle-row'
                );

            if (filas.length === 1) {
                return;
            }

            event.target
                .closest('.compra-detalle-row')
                .remove();

            calcularTotal();
        }
    );

});

</script>

@endpush