@extends('layouts.gestion')

@section(
    'title',
    'Historial de stock | ' . $negocio->nombre
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
                Stock
            </span>

            <h1 class="mt-2 mb-1">
                Historial de movimientos
            </h1>

        </div>


        <a
            href="{{ route(
                'gestion.stock.index',
                $negocio
            ) }}"
            class="btn btn-outline-secondary"
        >
            Volver a stock
        </a>

    </div>


    <div class="contact-form-card mb-4">

        <form
            method="GET"
            class="row g-3 align-items-end"
        >

            <div class="col-md-8">

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
                >

                    <option value="">
                        Todos los productos
                    </option>

                    @foreach($productos as $producto)

                        <option
                            value="{{ $producto->id }}"
                            @selected(
                                request('producto_id')
                                == $producto->id
                            )
                        >
                            {{ $producto->nombre }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div class="col-md-4">

                <div class="d-flex gap-2">

                    <button
                        class="btn btn-acsoft-primary"
                    >
                        Filtrar
                    </button>

                    <a
                        href="{{ route(
                            'gestion.stock.history',
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

        @if($movimientos->isEmpty())

            <div class="demo-empty-state">

                <h2>
                    No hay movimientos
                </h2>

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
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Concepto</th>
                            <th>Usuario</th>
                            <th class="text-end">
                                Cantidad
                            </th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach(
                            $movimientos
                            as $movimiento
                        )

                            <tr>

                                <td>
                                    {{ $movimiento
                                        ->created_at
                                        ->format('d/m/Y H:i') }}
                                </td>

                                <td>
                                    {{ $movimiento
                                        ->producto
                                        ->nombre }}
                                </td>

                                <td>
                                    {{ ucfirst(
                                        $movimiento->tipo
                                    ) }}
                                </td>

                                <td>

                                    {{ $movimiento->concepto }}

                                    @if($movimiento->origen_tipo)

                                        <small
                                            class="d-block
                                            text-muted"
                                        >
                                            Automático ·
                                            {{ ucfirst(
                                                $movimiento
                                                    ->origen_tipo
                                            ) }}
                                        </small>

                                    @endif

                                </td>

                                <td>
                                    {{ $movimiento
                                        ->usuario?->name
                                        ?: '—' }}
                                </td>

                                <td class="text-end">

                                    @if(
                                        $movimiento->tipo
                                        === 'salida'
                                    )

                                        -

                                    @elseif(
                                        $movimiento->tipo
                                        === 'entrada'
                                    )

                                        +

                                    @endif

                                    {{ number_format(
                                        $movimiento->cantidad,
                                        3,
                                        ',',
                                        '.'
                                    ) }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>


    @if($movimientos->hasPages())

        <div class="mt-4">
            {{ $movimientos->links() }}
        </div>

    @endif

</div>

@endsection