@extends('layouts.demo')

@section('title', 'Ingresos | ACSoft Demo')

@section('content')

<div class="container py-4">

    <div class="demo-page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

        <div>

            <span class="demo-page-label">
                Recepción
            </span>

            <h1>
                Ingresos de mercadería
            </h1>

            <p>
                Revisa los ingresos registrados durante esta sesión demo.
            </p>

        </div>

        <a
            href="{{ route('demo.ingresos.create') }}"
            class="btn btn-acsoft-primary"
        >
            + Registrar ingreso
        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success demo-alert-success">
            {{ session('success') }}
        </div>

    @endif


    <div class="demo-content-card">

        @if($ingresos->isEmpty())

            <div class="demo-empty-state">

                <div class="demo-empty-icon">
                    +
                </div>

                <h2>
                    Todavía no hay ingresos
                </h2>

                <p>
                    Registra la primera recepción para comenzar
                    a construir el historial.
                </p>

                <a
                    href="{{ route('demo.ingresos.create') }}"
                    class="btn btn-acsoft-primary"
                >
                    Registrar ingreso
                </a>

            </div>

        @else

            <div class="demo-table-responsive">

                <table class="table demo-crud-table align-middle mb-0">

                    <thead>

                        <tr>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Producto</th>
                            <th>Cajas</th>
                            <th>Total</th>
                            <th class="text-end">
                                Acciones
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($ingresos as $ingreso)

                            <tr>

                                <td>
                                    {{ $ingreso->fecha->format('d/m/Y') }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $ingreso->proveedor->nombre }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $ingreso->producto->nombre }}
                                </td>

                                <td>
                                    <span class="demo-unit-badge">
                                        {{ $ingreso->cantidad_cajas }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="demo-ingreso-total">
                                        {{ number_format(
                                            $ingreso->peso_total,
                                            2,
                                            ',',
                                            '.'
                                        ) }} kg
                                    </strong>
                                </td>

                                <td>

                                    <div class="demo-table-actions">

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-demo-edit"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#detalle-{{ $ingreso->id }}"
                                        >
                                            Ver cajas
                                        </button>


                                        <form
                                            action="{{ route('demo.ingresos.destroy', $ingreso) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Eliminar este ingreso?')"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-demo-delete"
                                            >
                                                Eliminar
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>


                            <tr
                                class="collapse demo-detail-row"
                                id="detalle-{{ $ingreso->id }}"
                            >

                                <td colspan="6">

                                    <div class="demo-ingreso-details">

                                        @foreach($ingreso->detalles as $detalle)

                                            <div>

                                                <span>
                                                    Caja {{ $detalle->numero_caja }}
                                                </span>

                                                <strong>
                                                    {{ number_format(
                                                        $detalle->peso,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ) }} kg
                                                </strong>

                                            </div>

                                        @endforeach

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endsection