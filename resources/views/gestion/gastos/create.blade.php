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

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="contact-form-card">

            <form
                id="expense-create-form"
                action="{{ route(
                    'gestion.gastos.store',
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
                            class="form-control @error('fecha') is-invalid @enderror"
                            required
                        >

                        @error('fecha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

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
                            class="form-control @error('concepto') is-invalid @enderror"
                            placeholder="Ej: Compra de bolsas"
                            required
                        >

                        @error('concepto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

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
                            class="form-control @error('monto') is-invalid @enderror"
                            required
                        >

                        @error('monto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

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
                            class="form-select @error('categoria') is-invalid @enderror"
                        >

                            <option value="">
                                Sin categoría
                            </option>

                            <option value="Mercadería" @selected(old('categoria') === 'Mercadería')>
                                Mercadería
                            </option>

                            <option value="Servicios" @selected(old('categoria') === 'Servicios')>
                                Servicios
                            </option>

                            <option value="Transporte" @selected(old('categoria') === 'Transporte')>
                                Transporte
                            </option>

                            <option value="Insumos" @selected(old('categoria') === 'Insumos')>
                                Insumos
                            </option>

                            <option value="Arriendo" @selected(old('categoria') === 'Arriendo')>
                                Arriendo
                            </option>

                            <option value="Personal" @selected(old('categoria') === 'Personal')>
                                Personal
                            </option>

                            <option value="Otros" @selected(old('categoria') === 'Otros')>
                                Otros
                            </option>

                        </select>

                        @error('categoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

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
                            class="form-select @error('metodo_pago') is-invalid @enderror"
                        >

                            <option value="">
                                Sin especificar
                            </option>

                            <option value="Efectivo" @selected(old('metodo_pago') === 'Efectivo')>
                                Efectivo
                            </option>

                            <option value="Débito" @selected(old('metodo_pago') === 'Débito')>
                                Débito
                            </option>

                            <option value="Crédito" @selected(old('metodo_pago') === 'Crédito')>
                                Crédito
                            </option>

                            <option value="Transferencia" @selected(old('metodo_pago') === 'Transferencia')>
                                Transferencia
                            </option>

                        </select>

                        @error('metodo_pago')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

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
                            class="form-control @error('observacion') is-invalid @enderror"
                        >{{ old('observacion') }}</textarea>

                        @error('observacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

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
                        id="expense-submit-button"
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('expense-create-form');
        const submitButton = document.getElementById('expense-submit-button');

        if (!form || !submitButton) {
            return;
        }

        const originalText = submitButton.textContent;

        const enableSubmit = () => {
            delete form.dataset.submitting;
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        };

        form.addEventListener('invalid', enableSubmit, true);

        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                enableSubmit();
                return;
            }

            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';
            submitButton.disabled = true;
            submitButton.textContent = 'Guardando...';
        });

        window.addEventListener('pageshow', enableSubmit);
    });
</script>
@endpush
