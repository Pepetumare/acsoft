@extends('layouts.gestion')

@section(
    'title',
    'Cerrar caja | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div style="max-width: 750px;">

        <a
            href="{{ route(
                'gestion.caja.index',
                $negocio
            ) }}"
            class="demo-back-link"
        >
            ← Volver a caja
        </a>


        <span class="section-eyebrow">
            Caja
        </span>

        <h1 class="mt-2">
            Cerrar caja
        </h1>

        <p class="text-muted mb-4">
            Cuenta el dinero disponible
            y compara el resultado esperado.
        </p>


        <div class="row g-3 mb-4">

            <div class="col-md-6">

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


            <div class="col-md-6">

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


        <div class="contact-form-card">

            <form
                action="{{ route(
                    'gestion.caja.destroy',
                    $negocio
                ) }}"
                method="POST"
            >

                @csrf


                <div class="mb-3">

                    <label
                        for="saldo_contado"
                        class="form-label"
                    >
                        Dinero contado *
                    </label>

                    <input
                        type="number"
                        id="saldo_contado"
                        name="saldo_contado"
                        min="0"
                        step="1"
                        value="{{ old('saldo_contado') }}"
                        class="form-control"
                        required
                    >

                    <div class="form-text">
                        Ingresa el dinero real disponible
                        en la caja.
                    </div>

                </div>


                <div class="mb-4">

                    <label
                        for="observacion_cierre"
                        class="form-label"
                    >
                        Observación del cierre
                    </label>

                    <textarea
                        id="observacion_cierre"
                        name="observacion_cierre"
                        rows="4"
                        class="form-control"
                    >{{ old('observacion_cierre') }}</textarea>

                </div>


                <div class="demo-form-actions">

                    <a
                        href="{{ route(
                            'gestion.caja.index',
                            $negocio
                        ) }}"
                        class="btn btn-demo-cancel"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-outline-danger"
                    >
                        Confirmar cierre
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection