@extends('layouts.gestion')

@section(
    'title',
    'Abrir caja | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div style="max-width: 700px;">

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
            Abrir caja
        </h1>

        <p class="text-muted mb-4">
            Indica cuánto dinero hay al comenzar la jornada.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route(
                    'gestion.caja.store',
                    $negocio
                ) }}"
                method="POST"
            >

                @csrf


                <div class="mb-3">

                    <label
                        for="saldo_inicial"
                        class="form-label"
                    >
                        Saldo inicial *
                    </label>

                    <input
                        type="number"
                        id="saldo_inicial"
                        name="saldo_inicial"
                        value="{{ old('saldo_inicial', 0) }}"
                        min="0"
                        step="1"
                        class="form-control"
                        required
                    >

                </div>


                <div class="mb-4">

                    <label
                        for="observacion_apertura"
                        class="form-label"
                    >
                        Observación
                    </label>

                    <textarea
                        id="observacion_apertura"
                        name="observacion_apertura"
                        rows="4"
                        class="form-control"
                    >{{ old('observacion_apertura') }}</textarea>

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
                        class="btn btn-acsoft-primary"
                    >
                        Abrir caja
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection