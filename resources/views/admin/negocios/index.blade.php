@extends('layouts.admin')

@section('title', 'Negocios | ACSoft Administración')

@section('robots', 'noindex, nofollow')

@section('content')

<div class="container py-5">

    <div class="d-flex flex-column flex-md-row
        justify-content-between
        align-items-md-center
        gap-3 mb-4">

        <div>

            <span class="section-eyebrow">
                ACSoft Administración
            </span>

            <h1 class="mt-2 mb-1">
                Negocios
            </h1>

            <p class="text-muted mb-0">
                Administra negocios,
                módulos y accesos.
            </p>

        </div>

        <a
            href="{{ route('admin.negocios.create') }}"
            class="btn btn-acsoft-primary"
        >
            + Nuevo negocio
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

        @if($negocios->isEmpty())

            <div class="demo-empty-state">

                <div class="demo-empty-icon">
                    +
                </div>

                <h2>
                    Todavía no hay negocios
                </h2>

                <p>
                    Crea el primer negocio
                    y selecciona sus módulos.
                </p>

                <a
                    href="{{ route('admin.negocios.create') }}"
                    class="btn btn-acsoft-primary"
                >
                    Crear negocio
                </a>

            </div>

        @else

            <div class="demo-table-responsive">

                <table
                    class="table demo-crud-table
                    align-middle mb-0"
                >

                    <thead>

                        <tr>
                            <th>Negocio</th>
                            <th>Cliente</th>
                            <th>Módulos</th>
                            <th>Estado</th>
                            <th class="text-end">
                                Acciones
                            </th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach($negocios as $negocio)

                            <tr>

                                <td>

                                    <strong>
                                        {{ $negocio->nombre }}
                                    </strong>

                                    <small class="d-block text-muted">
                                        /gestion/{{ $negocio->slug }}
                                    </small>

                                </td>


                                <td>
                                    {{ $negocio->cliente->nombre }}
                                </td>


                                <td>

                                    <div class="demo-provider-tags">

                                        @forelse(
                                            $negocio->modulos
                                            as $modulo
                                        )

                                            <span>
                                                {{ $modulo->nombre }}
                                            </span>

                                        @empty

                                            <small class="text-muted">
                                                Sin módulos
                                            </small>

                                        @endforelse

                                    </div>

                                </td>


                                <td>

                                    @if($negocio->activo)

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
                                                'gestion.dashboard',
                                                $negocio
                                            ) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            target="_blank"
                                        >
                                            Abrir
                                        </a>


                                        <a
                                            href="{{ route(
                                                'admin.negocios.edit',
                                                $negocio
                                            ) }}"
                                            class="btn btn-sm btn-demo-edit"
                                        >
                                            Configurar
                                        </a>


                                        @if(
                                            !$negocio
                                                ->usuarios()
                                                ->exists()
                                        )

                                            <form
                                                action="{{ route(
                                                    'admin.negocios.destroy',
                                                    $negocio
                                                ) }}"
                                                method="POST"
                                                onsubmit="return confirm(
                                                    '¿Eliminar este negocio?'
                                                )"
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
        {{ $negocios->links() }}
    </div>

</div>

@endsection