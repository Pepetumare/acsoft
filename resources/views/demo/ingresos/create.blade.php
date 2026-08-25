@extends('layouts.demo')

@section('title', 'Nuevo ingreso | ACSoft Demo')

@section('content')

<div class="container py-4">

    <div class="demo-form-page">

        <div class="demo-form-page-header">

            <a
                href="{{ route('demo.ingresos.index') }}"
                class="demo-back-link"
            >
                ← Volver a ingresos
            </a>

            <span class="demo-page-label">
                Recepción
            </span>

            <h1>
                Registrar mercadería
            </h1>

            <p>
                Selecciona el proveedor y producto, indica cuántas cajas
                llegaron y registra el peso individual de cada una.
            </p>

        </div>


        @if($proveedores->isEmpty())

            <div class="demo-content-card">

                <div class="demo-empty-state">

                    <div class="demo-empty-icon">
                        !
                    </div>

                    <h2>
                        Primero necesitas un proveedor
                    </h2>

                    <p>
                        Crea proveedores y productos antes de registrar
                        una recepción de mercadería.
                    </p>

                    <a
                        href="{{ route('demo.proveedores.create') }}"
                        class="btn btn-acsoft-primary"
                    >
                        Crear proveedor
                    </a>

                </div>

            </div>

        @else

            <div class="demo-form-card">

                <form
                    action="{{ route('demo.ingresos.store') }}"
                    method="POST"
                    id="demoIngresoForm"
                >

                    @csrf


                    <div class="row g-4">

                        <div class="col-md-6">

                            <label
                                for="proveedor_id"
                                class="form-label"
                            >
                                Proveedor *
                            </label>

                            <select
                                id="proveedor_id"
                                name="proveedor_id"
                                class="form-select
                                    @error('proveedor_id') is-invalid @enderror"
                                required
                            >

                                <option value="">
                                    Selecciona proveedor
                                </option>

                                @foreach($proveedores as $proveedor)

                                    <option
                                        value="{{ $proveedor->id }}"
                                        @selected(
                                            old('proveedor_id')
                                            == $proveedor->id
                                        )
                                    >
                                        {{ $proveedor->nombre }}
                                    </option>

                                @endforeach

                            </select>

                            @error('proveedor_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-6">

                            <label
                                for="producto_id"
                                class="form-label"
                            >
                                Producto *
                            </label>

                            <select
                                id="producto_id"
                                name="producto_id"
                                class="form-select
                                    @error('producto_id') is-invalid @enderror"
                                required
                                disabled
                            >

                                <option value="">
                                    Primero selecciona proveedor
                                </option>

                            </select>

                            @error('producto_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-6">

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
                                value="{{ old('fecha', now()->format('Y-m-d')) }}"
                                class="form-control
                                    @error('fecha') is-invalid @enderror"
                                required
                            >

                            @error('fecha')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="col-md-6">

                            <label
                                for="cantidad_cajas"
                                class="form-label"
                            >
                                Cantidad de cajas *
                            </label>

                            <input
                                type="number"
                                id="cantidad_cajas"
                                name="cantidad_cajas"
                                value="{{ old('cantidad_cajas', 1) }}"
                                min="1"
                                max="100"
                                class="form-control
                                    @error('cantidad_cajas') is-invalid @enderror"
                                required
                            >

                            @error('cantidad_cajas')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    <div class="demo-weight-section mt-4">

                        <div class="demo-weight-header">

                            <div>

                                <span class="demo-page-label">
                                    Pesos
                                </span>

                                <h3>
                                    Peso individual por caja
                                </h3>

                                <p>
                                    Ingresa el peso en kilogramos de cada caja.
                                </p>

                            </div>

                            <div class="demo-weight-total">

                                <span>
                                    Total
                                </span>

                                <strong id="pesoTotal">
                                    0,00 kg
                                </strong>

                            </div>

                        </div>


                        @error('pesos')
                            <div class="alert alert-danger mt-3">
                                {{ $message }}
                            </div>
                        @enderror


                        <div
                            id="pesoInputs"
                            class="demo-weight-grid"
                        ></div>

                    </div>


                    <div class="demo-form-actions">

                        <a
                            href="{{ route('demo.ingresos.index') }}"
                            class="btn btn-demo-cancel"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-acsoft-primary"
                        >
                            Guardar ingreso
                        </button>

                    </div>

                </form>

            </div>

        @endif

    </div>

</div>

@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const proveedores = @json($productosPorProveedor);


    const proveedorSelect =
        document.getElementById('proveedor_id');

    const productoSelect =
        document.getElementById('producto_id');

    const cantidadInput =
        document.getElementById('cantidad_cajas');

    const pesoContainer =
        document.getElementById('pesoInputs');

    const pesoTotal =
        document.getElementById('pesoTotal');


    const productoAnterior = @js(old('producto_id'));


    function cargarProductos() {

        const proveedorId =
            proveedorSelect.value;

        productoSelect.innerHTML = '';

        if (
            !proveedorId
            || !proveedores[proveedorId]
            || proveedores[proveedorId].length === 0
        ) {

            productoSelect.disabled = true;

            productoSelect.innerHTML =
                '<option value="">Sin productos asociados</option>';

            return;
        }


        productoSelect.disabled = false;

        productoSelect.innerHTML =
            '<option value="">Selecciona producto</option>';


        proveedores[proveedorId]
            .forEach(function (producto) {

                const option =
                    document.createElement('option');

                option.value =
                    producto.id;

                option.textContent =
                    producto.nombre;

                if (
                    String(producto.id)
                    === String(productoAnterior)
                ) {
                    option.selected = true;
                }

                productoSelect.appendChild(option);

            });

    }


    function crearPesos() {

        const cantidad =
            Math.max(
                1,
                Math.min(
                    100,
                    parseInt(cantidadInput.value) || 1
                )
            );

        const valoresActuales = [];

        pesoContainer
            .querySelectorAll('input')
            .forEach(input => {

                valoresActuales.push(
                    input.value
                );

            });


        pesoContainer.innerHTML = '';


        for (
            let i = 0;
            i < cantidad;
            i++
        ) {

            const wrapper =
                document.createElement('div');

            wrapper.className =
                'demo-weight-item';


            const label =
                document.createElement('label');

            label.textContent =
                `Caja ${i + 1}`;


            const input =
                document.createElement('input');

            input.type = 'number';

            input.name = 'pesos[]';

            input.step = '0.01';

            input.min = '0.01';

            input.placeholder = '0,00';

            input.className =
                'form-control demo-weight-input';

            input.required = true;


            if (
                valoresActuales[i] !== undefined
            ) {

                input.value =
                    valoresActuales[i];

            }


            input.addEventListener(
                'input',
                calcularTotal
            );


            const unit =
                document.createElement('span');

            unit.textContent = 'kg';


            wrapper.appendChild(label);
            wrapper.appendChild(input);
            wrapper.appendChild(unit);

            pesoContainer.appendChild(wrapper);

        }


        calcularTotal();

    }


    function calcularTotal() {

        let total = 0;

        document
            .querySelectorAll('.demo-weight-input')
            .forEach(function (input) {

                total +=
                    parseFloat(input.value) || 0;

            });


        pesoTotal.textContent =
            total.toLocaleString(
                'es-CL',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }
            )
            + ' kg';

    }


    proveedorSelect
        ?.addEventListener(
            'change',
            cargarProductos
        );


    cantidadInput
        ?.addEventListener(
            'input',
            crearPesos
        );


    cargarProductos();

    crearPesos();

});

</script>

@endpush