@extends('layouts.gestion')

@section(
    'title',
    'Nuevo gasto | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div style="max-width: 850px;">

        <a
            href="{{ route(
                'gestion.gastos.index',
                $negocio
            ) }}"
            class="demo-back-link"
        >
            ← Volver a gastos
        </a>

        <span class="section-eyebrow">
            Finanzas
        </span>

        <h1 class="mt-2">
            Nuevo gasto
        </h1>

        <p class="text-muted mb-4">
            Registra una salida de dinero del negocio.
        </p>


        <div class="contact-form-card">

            <form
                action="{{ route(
                    'gestion.gastos.store',
                    $negocio
                ) }}"
                method="POST"
            >

                @csrf


                <div class="row g-3">

                    <div class="col-md-4">

                        <label
                            for="fecha"
                            class="form-label"
                        >
                            Fecha *
                        </label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            value="{{ old(
                                'fecha',
                                now()->toDateString()
                            ) }}"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-8">

                        <label
                            for="concepto"
                            class="form-label"
                        >
                            Concepto *
                        </label>

                        <input
                            type="text"
                            id="concepto"
                            name="concepto"
                            value="{{ old('concepto') }}"
                            class="form-control"
                            placeholder="Ej: Compra de bolsas"
                            required
                        >

                    </div>


                    <div class="col-md-4">

                        <label
                            for="monto"
                            class="form-label"
                        >
                            Monto *
                        </label>

                        <input
                            type="number"
                            id="monto"
                            name="monto"
                            value="{{ old('monto') }}"
                            min="1"
                            step="1"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-4">

                        <label
                            for="categoria"
                            class="form-label"
                        >
                            Categoría
                        </label>

                        <select
                            id="categoria"
                            name="categoria"
                            class="form-select"
                        >

                            <option value="">
                                Sin categoría
                            </option>

                            <option value="Mercadería">
                                Mercadería
                            </option>

                            <option value="Servicios">
                                Servicios
                            </option>

                            <option value="Transporte">
                                Transporte
                            </option>

                            <option value="Insumos">
                                Insumos
                            </option>

                            <option value="Arriendo">
                                Arriendo
                            </option>

                            <option value="Personal">
                                Personal
                            </option>

                            <option value="Otros">
                                Otros
                            </option>

                        </select>

                    </div>


                    <div class="col-md-4">

                        <label
                            for="metodo_pago"
                            class="form-label"
                        >
                            Método de pago
                        </label>

                        <select
                            id="metodo_pago"
                            name="metodo_pago"
                            class="form-select"
                        >

                            <option value="">
                                Sin especificar
                            </option>

                            <option value="Efectivo">
                                Efectivo
                            </option>

                            <option value="Débito">
                                Débito
                            </option>

                            <option value="Crédito">
                                Crédito
                            </option>

                            <option value="Transferencia">
                                Transferencia
                            </option>

                        </select>

                    </div>


                    <div class="col-12">

                        <label
                            for="observacion"
                            class="form-label"
                        >
                            Observación
                        </label>

                        <textarea
                            id="observacion"
                            name="observacion"
                            rows="4"
                            class="form-control"
                        >{{ old('observacion') }}</textarea>

                    </div>

                </div>


                <div class="demo-form-actions">

                    <a
                        href="{{ route(
                            'gestion.gastos.index',
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
                        Registrar gasto
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection