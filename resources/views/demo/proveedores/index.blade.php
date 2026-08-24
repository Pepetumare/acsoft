@extends('layouts.demo')

@section('title', 'Proveedores | ACSoft Demo')

@section('content')

<div class="container py-4">

    <div class="demo-page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

        <div>

            <span class="demo-page-label">
                Gestión
            </span>

            <h1>
                Proveedores
            </h1>

            <p>
                Registra y administra los proveedores disponibles
                para esta sesión de demostración.
            </p>

        </div>

        <div>

            <a
                href="{{ route('demo.proveedores.create') }}"
                class="btn btn-acsoft-primary"
            >
                + Nuevo proveedor
            </a>

        </div>

    </div>


    @if(session('success'))

        <div
            class="alert alert-success demo-alert-success"
            role="alert"
        >
            {{ session('success') }}
        </div>

    @endif


    <div class="demo-content-card">

        @if($proveedores->isEmpty())

            <div class="demo-empty-state">

                <div class="demo-empty-icon">
                    +
                </div>

                <h2>
                    Todavía no tienes proveedores
                </h2>

                <p>
                    Crea el primero para comenzar a registrar productos
                    e ingresos de mercadería.
                </p>

                <a
                    href="{{ route('demo.proveedores.create') }}"
                    class="btn btn-acsoft-primary"
                >
                    Crear proveedor
                </a>

            </div>

        @else

            <div class="demo-table-responsive">

                <table class="table demo-crud-table align-middle mb-0">

                    <thead>

                        <tr>
                            <th>
                                Proveedor
                            </th>

                            <th>
                                Registrado
                            </th>

                            <th class="text-end">
                                Acciones
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($proveedores as $proveedor)

                            <tr>

                                <td>

                                    <div class="demo-provider-cell">

                                        <div class="demo-provider-avatar">
                                            {{ strtoupper(substr($proveedor->nombre, 0, 1)) }}
                                        </div>

                                        <div>

                                            <strong>
                                                {{ $proveedor->nombre }}
                                            </strong>

                                            <small>
                                                ID #{{ $proveedor->id }}
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <span class="demo-date">
                                        {{ $proveedor->created_at->format('d/m/Y H:i') }}
                                    </span>

                                </td>


                                <td>

                                    <div class="demo-table-actions">

                                        <a
                                            href="{{ route('demo.proveedores.edit', $proveedor) }}"
                                            class="btn btn-sm btn-demo-edit"
                                        >
                                            Editar
                                        </a>


                                        <form
                                            action="{{ route('demo.proveedores.destroy', $proveedor) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Eliminar este proveedor?')"
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

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endsection