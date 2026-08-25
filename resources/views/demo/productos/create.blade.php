@extends('layouts.demo')

@section('title', 'Nuevo producto | ACSoft Demo')

@section('content')

<div class="container py-4">

    <div class="demo-form-page">

        <div class="demo-form-page-header">

            <a
                href="{{ route('demo.productos.index') }}"
                class="demo-back-link"
            >
                ← Volver a productos
            </a>

            <span class="demo-page-label">
                Productos
            </span>

            <h1>
                Nuevo producto
            </h1>

            <p>
                Registra un producto y selecciona los proveedores
                que pueden suministrarlo.
            </p>

        </div>


        <div class="demo-form-card">

            <form
                action="{{ route('demo.productos.store') }}"
                method="POST"
            >

                @csrf

                @include('demo.productos._form')


                <div class="demo-form-actions">

                    <a
                        href="{{ route('demo.productos.index') }}"
                        class="btn btn-demo-cancel"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Guardar producto
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection