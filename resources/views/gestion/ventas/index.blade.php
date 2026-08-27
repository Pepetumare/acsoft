@extends('layouts.gestion')

@section('title', 'Ventas | ' . $negocio->nombre)

@section('content')

    <div class="container-fluid p-4 p-lg-5">

        <div
            class="d-flex flex-column flex-md-row
        justify-content-between
        align-items-md-center
        gap-3 mb-4">

            <div>

                <span class="section-eyebrow">
                    Ventas
                </span>

                <h1 class="mt-2 mb-1">
                    Ventas
                </h1>

                <p class="text-muted mb-0">
                    Registra y revisa las ventas del negocio.
                </p>

            </div>

            <a href="{{ route('gestion.ventas.create', $negocio) }}"
                class="btn btn-acsoft-primary">
                + Nueva venta
            </a>

        </div>


        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif


        <div class="row g-3 mb-4">

            <div class="col-md-4">

                <div class="gestion-module-card h-100">

                    <div>

                        <small class="text-muted">
                            Ventas de hoy
                        </small>

                        <h2 class="h3 mt-2 mb-0">
                            ${{ number_format($totalHoy, 0, ',', '.') }}
                        </h2>

                    </div>

                </div>

            </div>

        </div>


        <div class="demo-content-card">

            <div class="contact-form-card mb-4">

                <form method="GET"
                    action="{{ route('gestion.ventas.index', $negocio) }}"
                    class="row g-3 align-items-end">

                    <div class="col-md-4">

                        <label for="desde" class="form-label">
                            Desde
                        </label>

                        <input type="date" id="desde" name="desde" value="{{ request('desde') }}"
                            class="form-control">

                    </div>


                    <div class="col-md-4">

                        <label for="hasta" class="form-label">
                            Hasta
                        </label>

                        <input type="date" id="hasta" name="hasta" value="{{ request('hasta') }}"
                            class="form-control">

                    </div>


                    <div class="col-md-4">

                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-acsoft-primary">
                                Filtrar
                            </button>

                            <a href="{{ route('gestion.ventas.index', $negocio) }}"
                                class="btn btn-outline-secondary">
                                Limpiar
                            </a>

                        </div>

                    </div>

                </form>

            </div>


            @if ($ventas->isEmpty())

                <div class="demo-empty-state">

                    <h2>
                        No hay ventas para mostrar
                    </h2>

                    <p>
                        Registra una venta o modifica
                        los filtros seleccionados.
                    </p>

                    <a href="{{ route('gestion.ventas.create', $negocio) }}"
                        class="btn btn-acsoft-primary">
                        Registrar venta
                    </a>

                </div>
            @else
                <div class="demo-table-responsive">

                    <table class="table demo-crud-table
                    align-middle mb-0">

                        <thead>

                            <tr>
                                <th>Fecha</th>
                                <th>Detalle</th>
                                <th>Pago</th>
                                <th>Usuario</th>
                                <th>Total</th>

                                <th class="text-end">
                                    Acciones
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($ventas as $venta)
                                <tr>

                                    <td>
                                        {{ $venta->fecha->format('d/m/Y') }}
                                    </td>


                                    <td>

                                        @forelse($venta->detalles
                                            as $detalle)
                                            <div>

                                                {{ $detalle->descripcion }}

                                                <small class="text-muted">
                                                    × {{ (float) $detalle->cantidad }}
                                                </small>

                                            </div>

                                        @empty

                                            <span class="text-muted">
                                                Sin detalle
                                            </span>
                                        @endforelse

                                    </td>


                                    <td>
                                        {{ $venta->metodo_pago ? ucfirst($venta->metodo_pago) : '—' }}
                                    </td>


                                    <td>
                                        {{ $venta->usuario?->name ?: '—' }}
                                    </td>


                                    <td>

                                        <strong>
                                            ${{ number_format($venta->total, 0, ',', '.') }}
                                        </strong>

                                    </td>


                                    <td class="text-end">

                                        <form
                                            action="{{ route('gestion.ventas.destroy', [$negocio, $venta]) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm(
                                            '¿Eliminar esta venta?'
                                        )">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-demo-delete">
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


        @if ($ventas->hasPages())
            <div class="mt-4">
                {{ $ventas->links() }}
            </div>
        @endif

    </div>

@endsection
