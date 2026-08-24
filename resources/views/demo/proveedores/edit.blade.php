@extends('layouts.demo')

@section('title', 'Editar proveedor | ACSoft Demo')

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
                Editar proveedor
            </h1>

            <p>
                Modifica el nombre del proveedor seleccionado.
            </p>

        </div>


        <div class="demo-form-card">

            <form
                action="{{ route('demo.proveedores.update', $proveedor) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


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
                        Guardar cambios
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection