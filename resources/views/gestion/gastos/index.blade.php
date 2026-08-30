@extends('layouts.gestion')

@section(
    'title',
    'Gastos | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>

            <span class="section-eyebrow">
                Finanzas
            </span>

            <h1 class="mt-2 mb-1">
                Gastos
            </h1>

            <p class="text-muted mb-0">
                Registra y revisa los egresos del negocio.
            </p>

        </div>

        <a
            href="{{ route(
                'gestion.gastos.create',
                $negocio
            ) }}"
            class="btn btn-acsoft-primary"
        >
            + Nuevo gasto
        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif

    @if(session('error'))

        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>

    @endif


    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="gestion-module-card h-100">

                <div>

                    <small class="text-muted">
                        Gastos de hoy
                    </small>

                    <h2 class="h3 mt-2 mb-0">
                        ${{ number_format(
                            $totalHoy,
                            0,
                            ',',
                            '.'
                        ) }}
                    </h2>

                </div>

            </div>

        </div>

    </div>


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

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Filtrar
                    </button>

                    <a
                        href="{{ route(
                            'gestion.gastos.index',
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

        @if($gastos->isEmpty())

            <div class="demo-empty-state">

                <h2>
                    No hay gastos registrados
                </h2>

                <p>
                    Registra el primer gasto del negocio.
                </p>

            </div>

        @else

            <div class="demo-table-responsive">

                <table class="table demo-crud-table align-middle mb-0">

                    <thead>

                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Categoría</th>
                            <th>Pago</th>
                            <th>Usuario</th>
                            <th>Monto</th>
                            <th class="text-end">
                                Acciones
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($gastos as $gasto)

                            <tr>

                                <td>
                                    {{ $gasto
                                        ->fecha
                                        ->format('d/m/Y') }}
                                </td>

                                <td>

                                    <strong>
                                        {{ $gasto->concepto }}
                                    </strong>

                                    @if($gasto->observacion)

                                        <small class="d-block text-muted">
                                            {{ $gasto->observacion }}
                                        </small>

                                    @endif

                                </td>

                                <td>
                                    {{ $gasto->categoria ?: '—' }}
                                </td>

                                <td>
                                    {{ $gasto->metodo_pago ?: '—' }}
                                </td>

                                <td>
                                    {{ $gasto->usuario?->name ?: '—' }}
                                </td>

                                <td>
                                    <strong>
                                        ${{ number_format(
                                            $gasto->monto,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>
                                </td>

                                <td class="text-end">

                                    <form
                                        action="{{ route(
                                            'gestion.gastos.destroy',
                                            [
                                                $negocio,
                                                $gasto
                                            ]
                                        ) }}"
                                        method="POST"
                                        onsubmit="return confirm(
                                            '¿Eliminar este gasto?'
                                        )"
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

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>


    <div class="mt-4">
        {{ $gastos->links() }}
    </div>

</div>

@endsection
