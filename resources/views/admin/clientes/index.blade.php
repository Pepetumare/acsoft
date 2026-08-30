@extends('layouts.admin')

@section('title', 'Clientes | ACSoft Administración')

@section('robots', 'noindex, nofollow')

@section('content')

<div class="container py-5">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>

            <span class="section-eyebrow">
                ACSoft Administración
            </span>

            <h1 class="mt-2 mb-1">
                Clientes
            </h1>

            <p class="text-muted mb-0">
                Administra las personas o empresas que utilizan ACSoft.
            </p>

        </div>

        <a
            href="{{ route('admin.clientes.create') }}"
            class="btn btn-acsoft-primary"
        >
            + Nuevo cliente
        </a>

    </div>


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


    <div class="demo-content-card">

        @if($clientes->isEmpty())

            <div class="demo-empty-state">

                <div class="demo-empty-icon">
                    +
                </div>

                <h2>
                    Todavía no tienes clientes
                </h2>

                <p>
                    Crea tu primer cliente para comenzar a
                    configurar negocios y módulos.
                </p>

                <a
                    href="{{ route('admin.clientes.create') }}"
                    class="btn btn-acsoft-primary"
                >
                    Crear cliente
                </a>

            </div>

        @else

            <div class="demo-table-responsive">

                <table class="table demo-crud-table align-middle mb-0">

                    <thead>

                        <tr>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Negocios</th>
                            <th>Estado</th>
                            <th class="text-end">
                                Acciones
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($clientes as $cliente)

                            <tr>

                                <td>
                                    <strong>
                                        {{ $cliente->nombre }}
                                    </strong>
                                </td>

                                <td>

                                    <div>
                                        {{ $cliente->email ?: '—' }}
                                    </div>

                                    @if($cliente->telefono)

                                        <small class="text-muted">
                                            {{ $cliente->telefono }}
                                        </small>

                                    @endif

                                </td>

                                <td>
                                    {{ $cliente->negocios_count }}
                                </td>

                                <td>

                                    @if($cliente->activo)

                                        <span class="badge text-bg-success">
                                            Activo
                                        </span>

                                    @else

                                        <span class="badge text-bg-secondary">
                                            Inactivo
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    <div class="demo-table-actions">

                                        <a
                                            href="{{ route(
                                                'admin.clientes.edit',
                                                $cliente
                                            ) }}"
                                            class="btn btn-sm btn-demo-edit"
                                        >
                                            Editar
                                        </a>

                                        @if($cliente->negocios_count === 0)

                                            <form
                                                action="{{ route(
                                                    'admin.clientes.destroy',
                                                    $cliente
                                                ) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Eliminar este cliente?')"
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

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>


    <div class="mt-4">
        {{ $clientes->links() }}
    </div>

</div>

@endsection