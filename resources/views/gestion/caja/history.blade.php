@extends('layouts.gestion')

@section(
    'title',
    'Historial de caja | ' . $negocio->nombre
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
                Caja
            </span>

            <h1 class="mt-2 mb-1">
                Historial
            </h1>

            <p class="text-muted mb-0">
                Revisa los cierres anteriores.
            </p>

        </div>


        <a
            href="{{ route(
                'gestion.caja.index',
                $negocio
            ) }}"
            class="btn btn-outline-secondary"
        >
            Volver a caja
        </a>

    </div>


    <div class="demo-content-card">

        @if($cajas->isEmpty())

            <div class="demo-empty-state">

                <h2>
                    No hay cierres de caja
                </h2>

                <p>
                    Los cierres aparecerán aquí.
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
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Inicial</th>
                            <th>Esperado</th>
                            <th>Contado</th>
                            <th>Diferencia</th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach($cajas as $caja)

                            <tr>

                                <td>
                                    {{ $caja->fecha->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $caja->usuarioApertura?->name
                                        ?: '—' }}
                                </td>

                                <td>
                                    {{ $caja->usuarioCierre?->name
                                        ?: '—' }}
                                </td>

                                <td>
                                    ${{ number_format(
                                        $caja->saldo_inicial,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td>
                                    ${{ number_format(
                                        $caja->saldo_esperado,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td>
                                    ${{ number_format(
                                        $caja->saldo_contado,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td>

                                    @if($caja->diferencia > 0)

                                        <span
                                            class="badge text-bg-success"
                                        >
                                            +${{ number_format(
                                                $caja->diferencia,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @elseif($caja->diferencia < 0)

                                        <span
                                            class="badge text-bg-danger"
                                        >
                                            -${{ number_format(
                                                abs($caja->diferencia),
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @else

                                        <span
                                            class="badge text-bg-secondary"
                                        >
                                            $0
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


    @if($cajas->hasPages())

        <div class="mt-4">
            {{ $cajas->links() }}
        </div>

    @endif

</div>

@endsection