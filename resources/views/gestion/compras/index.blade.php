@extends('layouts.gestion')

@section(
    'title',
    'Compras | ' . $negocio->nombre
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
                Compras
            </h1>

            <p class="text-muted mb-0">
                Registra compras y entradas de productos.
            </p>

        </div>


        <a
            href="{{ route(
                'gestion.compras.create',
                $negocio
            ) }}"
            class="btn btn-acsoft-primary"
        >
            + Nueva compra
        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    <div class="contact-form-card mb-4">

        <form
            method="GET"
            class="row g-3 align-items-end"
        >

            <div class="col-md-4">

                <label class="form-label">
                    Desde
                </label>

                <input
                    type="date"
                    name="desde"
                    value="{{ request('desde') }}"
                    class="form-control"
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Hasta
                </label>

                <input
                    type="date"
                    name="hasta"
                    value="{{ request('hasta') }}"
                    class="form-control"
                >

            </div>


            <div class="col-md-4">

                <div class="d-flex flex-wrap gap-2">

                    <button
                        class="btn btn-acsoft-primary"
                    >
                        Filtrar
                    </button>

                    <a
                        href="{{ route(
                            'gestion.compras.index',
                            $negocio
                        ) }}"
                        class="btn btn-outline-secondary"
                    >
                        Limpiar
                    </a>

                </div>

            </div>

        </form>

    </div>


    <div class="demo-content-card">

        @if($compras->isEmpty())

            <div class="demo-empty-state">

                <h2>
                    No hay compras para mostrar
                </h2>

                <p>
                    Registra la primera compra del negocio.
                </p>

            </div>

        @else

            <div class="table-responsive">

                <table
                    class="table demo-crud-table
                    align-middle mb-0"
                >

                    <thead>

                        <tr>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Detalle</th>
                            <th>Usuario</th>
                            <th>Total</th>

                            <th class="text-end">
                                Acciones
                            </th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach($compras as $compra)

                            <tr>

                                <td>
                                    {{ $compra
                                        ->fecha
                                        ->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $compra->proveedor ?: '—' }}
                                </td>

                                <td>

                                    @foreach(
                                        $compra->detalles
                                        as $detalle
                                    )

                                        <div class="mb-2">

                                            <div class="fw-semibold">
                                                {{ $detalle
                                                    ->producto
                                                    ->nombre }}
                                            </div>

                                            <small class="text-muted">
                                                {{ (float) $detalle->cantidad }}
                                                ×
                                                ${{ number_format(
                                                    $detalle->costo_unitario,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}
                                                =
                                                <span class="fw-semibold">
                                                    ${{ number_format(
                                                        $detalle->subtotal,
                                                        0,
                                                        ',',
                                                        '.'
                                                    ) }}
                                                </span>
                                            </small>

                                        </div>

                                    @endforeach

                                </td>

                                <td>
                                    {{ $compra
                                        ->usuario?->name
                                        ?: '—' }}
                                </td>

                                <td>

                                    <strong>
                                        ${{ number_format(
                                            $compra->total,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>

                                </td>

                                <td class="text-end">

                                    <form
                                        action="{{ route(
                                            'gestion.compras.destroy',
                                            [
                                                $negocio,
                                                $compra
                                            ]
                                        ) }}"
                                        method="POST"
                                        onsubmit="return confirm(
                                            '¿Eliminar esta compra?'
                                        )"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="btn btn-sm
                                                btn-demo-delete"
                                        >
                                            Eliminar
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>


    @if($compras->hasPages())

        <div class="mt-4">
            {{ $compras->links() }}
        </div>

    @endif

</div>

@endsection
