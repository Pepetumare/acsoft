@extends('layouts.gestion')

@section(
    'title',
    'Caja | ' . $negocio->nombre
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
                Finanzas
            </span>

            <h1 class="mt-2 mb-1">
                Caja
            </h1>

            <p class="text-muted mb-0">
                Controla aperturas, movimientos y cierres.
            </p>

        </div>


        <a
            href="{{ route(
                'gestion.caja.history',
                $negocio
            ) }}"
            class="btn btn-outline-secondary"
        >
            Historial
        </a>

    </div>


    {{-- Mensajes --}}
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


    {{-- Sin caja abierta --}}
    @if(!$caja)

        <div class="demo-content-card">

            <div class="demo-empty-state">

                <h2>
                    Caja cerrada
                </h2>

                <p>
                    No hay ninguna caja abierta actualmente.
                </p>

                <a
                    href="{{ route(
                        'gestion.caja.create',
                        $negocio
                    ) }}"
                    class="btn btn-acsoft-primary"
                >
                    Abrir caja
                </a>

            </div>

        </div>


    {{-- Caja abierta --}}
    @else

        @php

            $totalIngresos =
                $caja->totalIngresos();

            $totalEgresos =
                $caja->totalEgresos();

            $saldoEsperado =
                $caja->calcularSaldoEsperado();

        @endphp


        {{-- Resumen --}}
        <div class="row g-4 mb-4">

            {{-- Saldo inicial --}}
            <div class="col-md-6 col-xl-3">

                <div class="gestion-module-card h-100">

                    <div>

                        <small class="text-muted">
                            Saldo inicial
                        </small>

                        <h2 class="h3 mt-2 mb-0">
                            ${{ number_format(
                                $caja->saldo_inicial,
                                0,
                                ',',
                                '.'
                            ) }}
                        </h2>

                    </div>

                </div>

            </div>


            {{-- Ingresos --}}
            <div class="col-md-6 col-xl-3">

                <div class="gestion-module-card h-100">

                    <div>

                        <small class="text-muted">
                            Ingresos
                        </small>

                        <h2 class="h3 mt-2 mb-0">
                            ${{ number_format(
                                $totalIngresos,
                                0,
                                ',',
                                '.'
                            ) }}
                        </h2>

                    </div>

                </div>

            </div>


            {{-- Egresos --}}
            <div class="col-md-6 col-xl-3">

                <div class="gestion-module-card h-100">

                    <div>

                        <small class="text-muted">
                            Egresos
                        </small>

                        <h2 class="h3 mt-2 mb-0">
                            ${{ number_format(
                                $totalEgresos,
                                0,
                                ',',
                                '.'
                            ) }}
                        </h2>

                    </div>

                </div>

            </div>


            {{-- Saldo esperado --}}
            <div class="col-md-6 col-xl-3">

                <div class="gestion-module-card h-100">

                    <div>

                        <small class="text-muted">
                            Saldo esperado
                        </small>

                        <h2 class="h3 mt-2 mb-0">
                            ${{ number_format(
                                $saldoEsperado,
                                0,
                                ',',
                                '.'
                            ) }}
                        </h2>

                    </div>

                </div>

            </div>

        </div>


        <div class="row g-4">

            {{-- Registrar movimiento --}}
            <div class="col-lg-5">

                <div class="contact-form-card">

                    <h2 class="h5 mb-4">
                        Registrar movimiento
                    </h2>


                    <form
                        id="manual-cash-movement-form"
                        action="{{ route(
                            'gestion.caja.movimientos.store',
                            $negocio
                        ) }}"
                        method="POST"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="operation_token"
                            value="{{ old('operation_token', (string) \Illuminate\Support\Str::uuid()) }}"
                        >


                        {{-- Tipo --}}
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
                                class="form-select
                                    @error('tipo') is-invalid @enderror"
                                required
                            >

                                <option
                                    value="ingreso"
                                    @selected(
                                        old('tipo') === 'ingreso'
                                    )
                                >
                                    Ingreso
                                </option>

                                <option
                                    value="egreso"
                                    @selected(
                                        old('tipo') === 'egreso'
                                    )
                                >
                                    Egreso
                                </option>

                            </select>

                            @error('tipo')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Concepto --}}
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
                                class="form-control
                                    @error('concepto') is-invalid @enderror"
                                placeholder="Ej: Pago de proveedor"
                                required
                            >

                            @error('concepto')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Monto --}}
                        <div class="mb-3">

                            <label
                                for="monto"
                                class="form-label"
                            >
                                Monto
                            </label>

                            <input
                                type="number"
                                id="monto"
                                name="monto"
                                value="{{ old('monto') }}"
                                min="1"
                                step="1"
                                class="form-control
                                    @error('monto') is-invalid @enderror"
                                required
                            >

                            @error('monto')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Observación --}}
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
                                class="form-control
                                    @error('observacion') is-invalid @enderror"
                            >{{ old('observacion') }}</textarea>

                            @error('observacion')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <button
                            type="submit"
                            id="manual-cash-movement-submit"
                            class="btn btn-acsoft-primary"
                        >
                            Registrar movimiento
                        </button>

                    </form>

                </div>

            </div>


            {{-- Movimientos --}}
            <div class="col-lg-7">

                <div class="demo-content-card">

                    {{-- Encabezado --}}
                    <div class="p-4 border-bottom">

                        <div
                            class="d-flex flex-column flex-sm-row
                            justify-content-between
                            align-items-sm-center
                            gap-3"
                        >

                            <div>

                                <h2 class="h5 mb-1">
                                    Movimientos
                                </h2>

                                <small class="text-muted">
                                    Caja abierta por
                                    {{ $caja->usuarioApertura?->name ?: '—' }}
                                </small>

                            </div>


                            <a
                                href="{{ route(
                                    'gestion.caja.close',
                                    $negocio
                                ) }}"
                                class="btn btn-outline-danger"
                            >
                                Cerrar caja
                            </a>

                        </div>

                    </div>


                    {{-- Sin movimientos --}}
                    @if($caja->movimientos->isEmpty())

                        <div class="p-4 text-muted">
                            Todavía no hay movimientos.
                        </div>


                    {{-- Tabla --}}
                    @else

                        <div class="table-responsive">

                            <table
                                class="table
                                align-middle mb-0"
                            >

                                <thead>

                                    <tr>
                                        <th>Hora</th>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Usuario</th>

                                        <th class="text-end">
                                            Monto
                                        </th>
                                    </tr>

                                </thead>


                                <tbody>

                                    @foreach(
                                        $caja->movimientos
                                        as $movimiento
                                    )

                                        <tr>

                                            {{-- Hora --}}
                                            <td>
                                                {{ $movimiento
                                                    ->created_at
                                                    ->format('H:i') }}
                                            </td>


                                            {{-- Tipo --}}
                                            <td>

                                                @if(
                                                    $movimiento->tipo
                                                    === 'ingreso'
                                                )

                                                    <span
                                                        class="badge
                                                        text-bg-success"
                                                    >
                                                        Ingreso
                                                    </span>

                                                @else

                                                    <span
                                                        class="badge
                                                        text-bg-danger"
                                                    >
                                                        Egreso
                                                    </span>

                                                @endif

                                            </td>


                                            {{-- Concepto --}}
                                            <td>

                                                <div>
                                                    {{ $movimiento->concepto }}
                                                </div>

                                                @if(
                                                    $movimiento->origen_tipo
                                                )

                                                    <small
                                                        class="d-block
                                                        text-muted mt-1"
                                                    >
                                                        Automático ·
                                                        {{ ucfirst(
                                                            $movimiento
                                                                ->origen_tipo
                                                        ) }}
                                                    </small>

                                                @else

                                                    <small
                                                        class="d-block
                                                        text-muted mt-1"
                                                    >
                                                        Manual
                                                    </small>

                                                @endif

                                                @if(
                                                    $movimiento->observacion
                                                )

                                                    <small
                                                        class="d-block
                                                        text-muted"
                                                    >
                                                        {{ $movimiento
                                                            ->observacion }}
                                                    </small>

                                                @endif

                                            </td>


                                            {{-- Usuario --}}
                                            <td>
                                                {{ $movimiento
                                                    ->usuario?->name
                                                    ?: '—' }}
                                            </td>


                                            {{-- Monto --}}
                                            <td class="text-end">

                                                @if(
                                                    $movimiento->tipo
                                                    === 'ingreso'
                                                )

                                                    <strong>
                                                        +${{ number_format(
                                                            $movimiento->monto,
                                                            0,
                                                            ',',
                                                            '.'
                                                        ) }}
                                                    </strong>

                                                @else

                                                    <strong>
                                                        -${{ number_format(
                                                            $movimiento->monto,
                                                            0,
                                                            ',',
                                                            '.'
                                                        ) }}
                                                    </strong>

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

    @endif

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('manual-cash-movement-form');
    const button = document.getElementById('manual-cash-movement-submit');

    if (!form || !button) return;

    const originalText = button.textContent;

    form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            button.disabled = false;
            button.textContent = originalText;
            return;
        }

        button.disabled = true;
        button.textContent = 'Guardando...';
    });

    window.addEventListener('pageshow', () => {
        button.disabled = false;
        button.textContent = originalText;
    });
});
</script>
@endpush
