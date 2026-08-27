@extends('layouts.admin')

@section('title', 'Usuarios | ACSoft Administración')

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
                Usuarios
            </h1>

            <p class="text-muted mb-0">
                Administra cuentas y accesos a negocios.
            </p>

        </div>

        <a
            href="{{ route('admin.usuarios.create') }}"
            class="btn btn-acsoft-primary"
        >
            + Nuevo usuario
        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    <div class="demo-content-card">

        @if($usuarios->isEmpty())

            <div class="demo-empty-state">

                <div class="demo-empty-icon">
                    +
                </div>

                <h2>
                    No hay usuarios
                </h2>

                <p>
                    Crea una cuenta y asígnala a un negocio.
                </p>

                <a
                    href="{{ route('admin.usuarios.create') }}"
                    class="btn btn-acsoft-primary"
                >
                    Crear usuario
                </a>

            </div>

        @else

            <div class="demo-table-responsive">

                <table class="table demo-crud-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Negocios</th>
                            <th>Rol</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($usuarios as $usuario)

                            <tr>

                                <td>

                                    <strong>
                                        {{ $usuario->name }}
                                    </strong>

                                    <small class="d-block text-muted">
                                        {{ $usuario->email }}
                                    </small>

                                </td>


                                <td>

                                    <div class="demo-provider-tags">

                                        @forelse(
                                            $usuario->negocios
                                            as $negocio
                                        )

                                            <span>
                                                {{ $negocio->nombre }}
                                            </span>

                                        @empty

                                            <small class="text-muted">
                                                Sin negocio
                                            </small>

                                        @endforelse

                                    </div>

                                </td>


                                <td>

                                    @if($usuario->negocios->isNotEmpty())

                                        <span class="badge text-bg-light">
                                            {{ $usuario
                                                ->negocios
                                                ->first()
                                                ->pivot
                                                ->rol }}
                                        </span>

                                    @else

                                        —

                                    @endif

                                </td>


                                <td>

                                    <div class="demo-table-actions">

                                        <a
                                            href="{{ route(
                                                'admin.usuarios.edit',
                                                $usuario
                                            ) }}"
                                            class="btn btn-sm btn-demo-edit"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            action="{{ route(
                                                'admin.usuarios.destroy',
                                                $usuario
                                            ) }}"
                                            method="POST"
                                            onsubmit="return confirm(
                                                '¿Eliminar este usuario?'
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
        {{ $usuarios->links() }}
    </div>

</div>

@endsection