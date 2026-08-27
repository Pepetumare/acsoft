@extends('layouts.gestion')

@section(
    'title',
    'Stock | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div
        class="d-flex flex-column flex-md-row
        justify-content-between
        align-items-md-center
        gap-3 mb-4"
    >

        <div>

            <span class="section-eyebrow">
                Inventario
            </span>

            <h1 class="mt-2 mb-1">
                Stock
            </h1>

            <p class="text-muted mb-0">
                Controla existencias, entradas,
                salidas y ajustes.
            </p>

        </div>

        <a
            href="{{ route(
                'gestion.stock.history',
                $negocio
            ) }}"
            class="btn btn-outline-secondary"
        >
            Historial
        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger">
            {{ session('error') }}
        </div>

    @endif


    <div class="row g-4">

        <div class="col-lg-5">

            <div class="contact-form-card">

                <h2 class="h5 mb-4">
                    Registrar movimiento
                </h2>

                <form
                    action="{{ route(
                        'gestion.stock.store',
                        $negocio
                    ) }}"
                    method="POST"
                >

                    @csrf


                    <div class="mb-3">

                        <label
                            for="producto_id"
                            class="form-label"
                        >
                            Producto
                        </label>

                        <select
                            id="producto_id"
                            name="producto_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecciona un producto
                            </option>

                            @foreach($productos as $producto)

                                <option
                                    value="{{ $producto->id }}"
                                    @selected(
                                        old('producto_id')
                                        == $producto->id
                                    )
                                >
                                    {{ $producto->nombre }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="mb-3">

                        <label
                            for="tipo"
                            class="form-label"
                        >
                            Tipo
                        </label>

                        <select
                            id="tipo"
                            name="tipo"
                            class="form-select"
                            required
                        >

                            <option value="entrada">
                                Entrada
                            </option>

                            <option value="salida">
                                Salida
                            </option>

                            <option value="ajuste">
                                Ajuste
                            </option>

                        </select>

                    </div>


                    <div class="mb-3">

                        <label
                            for="cantidad"
                            class="form-label"
                        >
                            Cantidad
                        </label>

                        <input
                            type="number"
                            id="cantidad"
                            name="cantidad"
                            value="{{ old('cantidad') }}"
                            step="0.001"
                            class="form-control"
                            required
                        >

                        <div class="form-text">
                            Para ajustes puedes usar
                            valores positivos o negativos.
                        </div>

                    </div>


                    <div class="mb-3">

                        <label
                            for="concepto"
                            class="form-label"
                        >
                            Concepto
                        </label>

                        <input
                            type="text"
                            id="concepto"
                            name="concepto"
                            value="{{ old('concepto') }}"
                            class="form-control"
                            placeholder="Ej: Inventario inicial"
                            required
                        >

                    </div>


                    <div class="mb-4">

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


                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Registrar movimiento
                    </button>

                </form>

            </div>

        </div>


        <div class="col-lg-7">

            <div class="demo-content-card">

                <div class="p-4 border-bottom">

                    <h2 class="h5 mb-0">
                        Existencias
                    </h2>

                </div>


                @if($productos->isEmpty())

                    <div class="p-4 text-muted">
                        No hay productos activos.
                    </div>

                @else

                    <div class="table-responsive">

                        <table
                            class="table
                            align-middle mb-0"
                        >

                            <thead>

                                <tr>
                                    <th>Producto</th>
                                    <th>Unidad</th>
                                    <th class="text-end">
                                        Stock
                                    </th>
                                    <th>Estado</th>
                                </tr>

                            </thead>


                            <tbody>

                                @foreach($productos as $producto)

                                    <tr>

                                        <td>
                                            <strong>
                                                {{ $producto->nombre }}
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $producto->unidad }}
                                        </td>

                                        <td class="text-end">

                                            <strong>
                                                {{ number_format(
                                                    $producto->stock_actual,
                                                    3,
                                                    ',',
                                                    '.'
                                                ) }}
                                            </strong>

                                        </td>

                                        <td>

                                            @if(
                                                $producto->stock_minimo !== null
                                                && $producto->stock_actual
                                                <= $producto->stock_minimo
                                            )

                                                <span
                                                    class="badge
                                                    text-bg-warning"
                                                >
                                                    Stock bajo
                                                </span>

                                            @else

                                                <span
                                                    class="badge
                                                    text-bg-success"
                                                >
                                                    Normal
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection