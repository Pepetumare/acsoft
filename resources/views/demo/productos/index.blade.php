@extends('layouts.demo')

@section('title', 'Productos | ACSoft Demo')

@section('content')

<div class="container py-4">

    <div class="demo-page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">

        <div>

            <span class="demo-page-label">
                Gestión
            </span>

            <h1>
                Productos
            </h1>

            <p>
                Administra los productos disponibles en esta sesión demo.
            </p>

        </div>

        <a
            href="{{ route('demo.productos.create') }}"
            class="btn btn-acsoft-primary"
        >
            + Nuevo producto
        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success demo-alert-success">
            {{ session('success') }}
        </div>

    @endif


    <div class="demo-content-card">

        @if($productos->isEmpty())

            <div class="demo-empty-state">

                <div class="demo-empty-icon">
                    +
                </div>

                <h2>
                    Todavía no tienes productos
                </h2>

                <p>
                    Crea un producto para comenzar a preparar
                    los ingresos de mercadería.
                </p>

                <a
                    href="{{ route('demo.productos.create') }}"
                    class="btn btn-acsoft-primary"
                >
                    Crear producto
                </a>

            </div>

        @else

            <div class="demo-table-responsive">

                <table class="table demo-crud-table align-middle mb-0">

                    <thead>

                        <tr>

                            <th>
                                Producto
                            </th>

                            <th>
                                Unidad
                            </th>

                            <th>
                                Proveedores
                            </th>

                            <th class="text-end">
                                Acciones
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($productos as $producto)

                            <tr>

                                <td>

                                    <div class="demo-provider-cell">

                                        <div class="demo-provider-avatar">
                                            {{ strtoupper(substr($producto->nombre, 0, 1)) }}
                                        </div>

                                        <div>

                                            <strong>
                                                {{ $producto->nombre }}
                                            </strong>

                                            <small>
                                                ID #{{ $producto->id }}
                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <td>

                                    <span class="demo-unit-badge">
                                        {{ $producto->unidad }}
                                    </span>

                                </td>


                                <td>

                                    <div class="demo-provider-tags">

                                        @forelse($producto->proveedores as $proveedor)

                                            <span>
                                                {{ $proveedor->nombre }}
                                            </span>

                                        @empty

                                            <small class="text-muted">
                                                Sin proveedores
                                            </small>

                                        @endforelse

                                    </div>

                                </td>


                                <td>

                                    <div class="demo-table-actions">

                                        <a
                                            href="{{ route('demo.productos.edit', $producto) }}"
                                            class="btn btn-sm btn-demo-edit"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="{{ route('demo.productos.destroy', $producto) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Eliminar este producto?')"
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