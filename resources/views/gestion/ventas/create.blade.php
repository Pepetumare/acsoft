@extends('layouts.gestion')

@section('title', 'Nueva venta | ' . $negocio->nombre)

@section('content')

    <div class="container-fluid p-4 p-lg-5">

        <div style="max-width: 1100px;">

            <a href="{{ route('gestion.ventas.index', $negocio) }}"
                class="demo-back-link">
                ← Volver a ventas
            </a>


            <span class="section-eyebrow">
                Ventas
            </span>

            <h1 class="mt-2">
                Nueva venta
            </h1>

            <p class="text-muted mb-4">

                @if ($usaProductos)
                    Selecciona productos del catálogo
                    o registra conceptos manualmente.
                @else
                    Agrega uno o varios conceptos
                    a la venta.
                @endif

            </p>


            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif


            <div class="contact-form-card">

                <form
                    action="{{ route('gestion.ventas.store', $negocio) }}"
                    method="POST">

                    @csrf


                    <div class="row g-3 mb-4">

                        <div class="col-md-4">

                            <label for="fecha" class="form-label">
                                Fecha *
                            </label>

                            <input type="date" id="fecha" name="fecha"
                                value="{{ old('fecha', now()->toDateString()) }}"
                                class="form-control" required>

                        </div>


                        <div class="col-md-4">

                            <label for="metodo_pago" class="form-label">
                                Método de pago
                            </label>

                            <select id="metodo_pago" name="metodo_pago"
                                class="form-select @error('metodo_pago') is-invalid @enderror">

                                <option value="">
                                    Sin especificar
                                </option>

                                @foreach (['Efectivo', 'Débito', 'Crédito', 'Transferencia'] as $metodo)
                                    <option value="{{ $metodo }}" @selected(old('metodo_pago') === $metodo)>
                                        {{ $metodo }}
                                    </option>
                                @endforeach

                            </select>

                            @error('metodo_pago')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    <h2 class="h5 mb-3">
                        Detalle
                    </h2>


                    @if ($usaProductos)

                        <div class="alert alert-light border mb-4">

                            Puedes seleccionar un producto
                            o dejarlo en

                            <strong>
                                Venta manual
                            </strong>

                            para escribir el concepto tú mismo.

                            @if ($usaStock)
                                El stock se descontará automáticamente
                                cuando selecciones un producto.
                            @endif

                        </div>

                    @endif


                    @php
                        $detallesAnteriores = old('detalles', [[
                            'producto_id' => '',
                            'descripcion' => '',
                            'cantidad' => 1,
                            'precio_unitario' => '',
                        ]]);
                        $siguienteIndiceDetalle = max(array_keys($detallesAnteriores)) + 1;
                    @endphp

                    <div id="detalles">

                        @foreach ($detallesAnteriores as $indiceDetalle => $detalleAnterior)

                        <div class="venta-detalle-row
                        row g-2 align-items-end mb-3">

                            @if ($usaProductos)

                                <div class="col-md-3">

                                    <label class="form-label">
                                        Producto
                                    </label>

                                    <select name="detalles[{{ $indiceDetalle }}][producto_id]"
                                        class="form-select venta-producto">

                                        <option value="">
                                            Venta manual
                                        </option>

                                        @foreach ($productos as $producto)
                                            <option value="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}"
                                                data-precio="{{ $producto->precio_venta ?? 0 }}"
                                                data-unidad="{{ strtolower(trim($producto->unidad)) }}"
                                                @selected((string) ($detalleAnterior['producto_id'] ?? '') === (string) $producto->id)>
                                                {{ $producto->nombre }}

                                                @if ($usaStock)
                                                    · Stock:
                                                    {{ number_format($producto->stock_actual, 3, ',', '.') }}
                                                @endif

                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                            @endif


                            <div
                                class="{{ $usaProductos ? 'col-md-3' : 'col-md-5' }}">

                                <label class="form-label">
                                    Descripción
                                </label>

                                <input type="text" name="detalles[{{ $indiceDetalle }}][descripcion]"
                                    value="{{ $detalleAnterior['descripcion'] ?? '' }}"
                                    class="form-control venta-descripcion" required>

                            </div>


                            <div class="col-md-2">

                                <label class="form-label">
                                    Cantidad
                                </label>

                                <input type="number" name="detalles[{{ $indiceDetalle }}][cantidad]"
                                    class="form-control venta-cantidad" min="0.001" step="0.001"
                                    value="{{ $detalleAnterior['cantidad'] ?? 1 }}" required>

                            </div>


                            <div class="col-md-2">

                                <label class="form-label">
                                    Precio unitario
                                </label>

                                <input type="number" name="detalles[{{ $indiceDetalle }}][precio_unitario]"
                                    class="form-control venta-precio" min="0" step="1"
                                    value="{{ $detalleAnterior['precio_unitario'] ?? '' }}" required>

                            </div>


                            <div class="col-md-2">

                                <button type="button"
                                    class="btn
                                btn-outline-danger
                                w-100 quitar-detalle">
                                    Quitar
                                </button>

                            </div>

                        </div>

                        @endforeach

                    </div>


                    <button type="button" id="agregar-detalle" class="btn btn-outline-secondary mb-4">
                        + Agregar concepto
                    </button>


                    <div
                        class="d-flex
                    justify-content-between
                    align-items-center
                    border-top pt-4">

                        <strong>
                            Total estimado
                        </strong>

                        <strong id="venta-total" class="fs-4">
                            $0
                        </strong>

                    </div>


                    <div class="mt-4">

                        <label for="observacion" class="form-label">
                            Observación
                        </label>

                        <textarea id="observacion" name="observacion" rows="3" class="form-control">{{ old('observacion') }}</textarea>

                    </div>


                    <div class="demo-form-actions">

                        <a href="{{ route('gestion.ventas.index', $negocio) }}"
                            class="btn btn-demo-cancel">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-acsoft-primary">
                            Registrar venta
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
                document.getElementById('detalles');

            const agregar =
                document.getElementById('agregar-detalle');

            const total =
                document.getElementById('venta-total');

            const usaProductos =
                @json($usaProductos);

            let indice = @json($siguienteIndiceDetalle);


            const opcionesProductos = `
        <option value="">
            Venta manual
        </option>

        @foreach ($productos as $producto)

            <option
                value="{{ $producto->id }}"
                data-nombre="{{ addslashes($producto->nombre) }}"
                data-precio="{{ $producto->precio_venta ?? 0 }}"
                data-unidad="{{ strtolower(trim($producto->unidad)) }}"
            >
                {{ addslashes($producto->nombre) }}

                @if ($usaStock)

                    · Stock:
                    {{ number_format($producto->stock_actual, 3, ',', '.') }}

                @endif
            </option>

        @endforeach
    `;


            function calcularTotal() {

                let valor = 0;

                contenedor
                    .querySelectorAll(
                        '.venta-detalle-row'
                    )
                    .forEach(fila => {

                        const cantidad =
                            parseFloat(
                                fila
                                .querySelector(
                                    '.venta-cantidad'
                                )
                                .value
                            ) || 0;

                        const precio =
                            parseFloat(
                                fila
                                .querySelector(
                                    '.venta-precio'
                                )
                                .value
                            ) || 0;

                        valor +=
                            cantidad * precio;
                    });


                total.textContent =
                    '$' +
                    Math.round(valor)
                    .toLocaleString('es-CL');
            }


            function completarProducto(select) {

                const fila =
                    select.closest(
                        '.venta-detalle-row'
                    );

                const descripcion =
                    fila.querySelector(
                        '.venta-descripcion'
                    );

                const precio =
                    fila.querySelector(
                        '.venta-precio'
                    );

                const cantidad =
                    fila.querySelector(
                        '.venta-cantidad'
                    );

                const opcion =
                    select.options[
                        select.selectedIndex
                    ];


                if (!select.value) {

                    cantidad.step = '0.001';

                    cantidad.min = '0.001';

                    descripcion.value = '';

                    precio.value = '';

                    descripcion.readOnly = false;

                    return;
                }

                const unidadesDiscretas = [
                    'unidad',
                    'caja',
                    'paquete'
                ];

                const cantidadEntera =
                    unidadesDiscretas.includes(
                        opcion.dataset.unidad
                    );

                cantidad.step = cantidadEntera ? '1' : '0.001';

                cantidad.min = cantidadEntera ? '1' : '0.001';


                descripcion.value =
                    opcion.dataset.nombre || '';

                precio.value =
                    opcion.dataset.precio || 0;

                descripcion.readOnly = true;

                calcularTotal();
            }


            agregar.addEventListener(
                'click',
                () => {

                    const fila =
                        document.createElement(
                            'div'
                        );

                    fila.className =
                        'venta-detalle-row ' +
                        'row g-2 ' +
                        'align-items-end mb-3';


                    const productoHtml =
                        usaProductos ?
                        `
                        <div class="col-md-3">

                            <label class="form-label">
                                Producto
                            </label>

                            <select
                                name="detalles[${indice}][producto_id]"
                                class="form-select venta-producto"
                            >
                                ${opcionesProductos}
                            </select>

                        </div>
                    ` :
                        '';


                    const descripcionClase =
                        usaProductos ?
                        'col-md-3' :
                        'col-md-5';


                    fila.innerHTML = `

                ${productoHtml}

                <div class="${descripcionClase}">

                    <label class="form-label">
                        Descripción
                    </label>

                    <input
                        type="text"
                        name="detalles[${indice}][descripcion]"
                        class="form-control venta-descripcion"
                        required
                    >

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Cantidad
                    </label>

                    <input
                        type="number"
                        name="detalles[${indice}][cantidad]"
                        class="form-control venta-cantidad"
                        min="0.001"
                        step="0.001"
                        value="1"
                        required
                    >

                </div>


                <div class="col-md-2">

                    <label class="form-label">
                        Precio unitario
                    </label>

                    <input
                        type="number"
                        name="detalles[${indice}][precio_unitario]"
                        class="form-control venta-precio"
                        min="0"
                        step="1"
                        required
                    >

                </div>


                <div class="col-md-2">

                    <button
                        type="button"
                        class="btn
                        btn-outline-danger
                        w-100 quitar-detalle"
                    >
                        Quitar
                    </button>

                </div>
            `;


                    contenedor.appendChild(
                        fila
                    );

                    indice++;
                }
            );


            contenedor.addEventListener(
                'change',
                event => {

                    if (
                        event.target
                        .classList
                        .contains(
                            'venta-producto'
                        )
                    ) {

                        completarProducto(
                            event.target
                        );
                    }
                }
            );

            contenedor
                .querySelectorAll('.venta-producto')
                .forEach(select => {
                    const fila = select.closest('.venta-detalle-row');
                    const opcion = select.options[select.selectedIndex];
                    const cantidad = fila.querySelector('.venta-cantidad');
                    const cantidadEntera = ['unidad', 'caja', 'paquete']
                        .includes(opcion?.dataset.unidad);

                    cantidad.step = cantidadEntera ? '1' : '0.001';
                    cantidad.min = cantidadEntera ? '1' : '0.001';
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
                        .contains(
                            'quitar-detalle'
                        )
                    ) {
                        return;
                    }


                    const filas =
                        contenedor
                        .querySelectorAll(
                            '.venta-detalle-row'
                        );


                    if (filas.length === 1) {
                        return;
                    }


                    event.target
                        .closest(
                            '.venta-detalle-row'
                        )
                        .remove();


                    calcularTotal();
                }
            );

        });
    </script>
@endpush
