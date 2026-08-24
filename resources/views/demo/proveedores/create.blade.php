@extends('layouts.demo')

@section('title', 'Nuevo proveedor | ACSoft Demo')

@section('content')

<div class="container py-4">

    <div class="demo-form-page">

        <div class="demo-form-page-header">

            <a
                href="{{ route('demo.proveedores.index') }}"
                class="demo-back-link"
            >
                ← Volver a proveedores
            </a>

            <span class="demo-page-label">
                Proveedores
            </span>

            <h1>
                Nuevo proveedor
            </h1>

            <p>
                Registra un proveedor para utilizarlo posteriormente
                al crear productos o ingresos.
            </p>

        </div>


        <div class="demo-form-card">

            <form
                action="{{ route('demo.proveedores.store') }}"
                method="POST"
            >

                @csrf

                @include('demo.proveedores._form')


                <div class="demo-form-actions">

                    <a
                        href="{{ route('demo.proveedores.index') }}"
                        class="btn btn-demo-cancel"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Guardar proveedor
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection