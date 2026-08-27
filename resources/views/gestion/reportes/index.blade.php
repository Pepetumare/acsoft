@extends('layouts.gestion')

@section(
    'title',
    'Reportes | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    {{-- Encabezado --}}
    <div
        class="d-flex flex-column flex-md-row
        justify-content-between
        align-items-md-center
        gap-3 mb-4"
    >

        <div>

            <span class="section-eyebrow">
                Análisis
            </span>

            <h1 class="mt-2 mb-1">
                Reportes
            </h1>

            <p class="text-muted mb-0">
                Revisa el comportamiento del negocio
                durante un período determinado.
            </p>

        </div>


        <div class="d-flex flex-wrap gap-2">

            <a
                href="{{ route(
                    'gestion.reportes.pdf',
                    [
                        'negocio' => $negocio,
                        'desde' => $desde->toDateString(),
                        'hasta' => $hasta->toDateString(),
                    ]
                ) }}"
                class="btn btn-outline-secondary"
            >
                Descargar PDF
            </a>

        </div>

    </div>


    {{-- Filtros --}}
    <div class="contact-form-card mb-4">

        <form
            method="GET"
            action="{{ route(
                'gestion.reportes.index',
                $negocio
            ) }}"
            class="row g-3 align-items-end"
        >

            <div class="col-md-4">

                <label
                    for="desde"
                    class="form-label"
                >
                    Desde
                </label>

                <input
                    type="date"
                    id="desde"
                    name="desde"
                    value="{{ $desde->toDateString() }}"
                    class="form-control"
                >

            </div>


            <div class="col-md-4">

                <label
                    for="hasta"
                    class="form-label"
                >
                    Hasta
                </label>

                <input
                    type="date"
                    id="hasta"
                    name="hasta"
                    value="{{ $hasta->toDateString() }}"
                    class="form-control"
                >

            </div>


            <div class="col-md-4">

                <div class="d-flex flex-wrap gap-2">

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Aplicar
                    </button>

                    <a
                        href="{{ route(
                            'gestion.reportes.index',
                            $negocio
                        ) }}"
                        class="btn btn-outline-secondary"
                    >
                        Mes actual
                    </a>

                </div>

            </div>

        </form>

    </div>


    {{-- Resumen principal --}}
    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="gestion-module-card h-100">

                <div>

                    <small class="text-muted">
                        Ventas
                    </small>

                    <h2 class="h3 mt-2 mb-0">
                        ${{ number_format(
                            $totalVentas,
                            0,
                            ',',
                            '.'
                        ) }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="gestion-module-card h-100">

                <div>

                    <small class="text-muted">
                        Gastos
                    </small>

                    <h2 class="h3 mt-2 mb-0">
                        ${{ number_format(
                            $totalGastos,
                            0,
                            ',',
                            '.'
                        ) }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="gestion-module-card h-100">

                <div>

                    <small class="text-muted">
                        Resultado
                    </small>

                    <h2 class="h3 mt-2 mb-0">
                        ${{ number_format(
                            $resultado,
                            0,
                            ',',
                            '.'
                        ) }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="gestion-module-card h-100">

                <div>

                    <small class="text-muted">
                        Ticket promedio
                    </small>

                    <h2 class="h3 mt-2 mb-0">
                        ${{ number_format(
                            $ticketPromedio,
                            0,
                            ',',
                            '.'
                        ) }}
                    </h2>

                    <small class="text-muted">
                        {{ $cantidadVentas }}
                        {{ $cantidadVentas === 1
                            ? 'venta'
                            : 'ventas' }}
                    </small>

                </div>

            </div>

        </div>

    </div>


    {{-- Detalles --}}
    <div class="row g-4">

        {{-- Ventas por método de pago --}}
        <div class="col-lg-6">

            <div class="demo-content-card h-100">

                <div class="p-4 border-bottom">

                    <h2 class="h5 mb-0">
                        Ventas por método de pago
                    </h2>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead>

                            <tr>

                                <th>
                                    Método
                                </th>

                                <th class="text-end">
                                    Total
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse(
                                $ventasPorMetodo
                                as $item
                            )

                                <tr>

                                    <td>
                                        {{ $item->metodo }}
                                    </td>

                                    <td class="text-end">

                                        <strong>
                                            ${{ number_format(
                                                $item->total,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </strong>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="2"
                                        class="text-center
                                        text-muted py-4"
                                    >
                                        No hay ventas
                                        en este período.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- Gastos por categoría --}}
        <div class="col-lg-6">

            <div class="demo-content-card h-100">

                <div class="p-4 border-bottom">

                    <h2 class="h5 mb-0">
                        Gastos por categoría
                    </h2>

                </div>


                <div class="table-responsive">

                    <table class="table align-middle mb-0">

                        <thead>

                            <tr>

                                <th>
                                    Categoría
                                </th>

                                <th class="text-end">
                                    Total
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse(
                                $gastosPorCategoria
                                as $item
                            )

                                <tr>

                                    <td>
                                        {{ $item->categoria_nombre }}
                                    </td>

                                    <td class="text-end">

                                        <strong>
                                            ${{ number_format(
                                                $item->total,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </strong>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="2"
                                        class="text-center
                                        text-muted py-4"
                                    >
                                        No hay gastos
                                        en este período.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection