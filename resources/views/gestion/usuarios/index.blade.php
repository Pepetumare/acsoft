@extends('layouts.gestion')

@section('title', 'Usuarios | ' . $negocio->nombre)

@section('content')
<div class="container-fluid p-4 p-lg-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <span class="section-eyebrow">Administración</span>
            <h1 class="mt-2 mb-1">Usuarios</h1>
            <p class="text-muted mb-0">Administra las personas con acceso a {{ $negocio->nombre }}.</p>
        </div>
        <a href="{{ route('gestion.usuarios.create', $negocio) }}" class="btn btn-acsoft-primary">
            + Nuevo usuario
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('invitation_url') && app()->environment(['local', 'development']))
        <div class="alert alert-info">
            Enlace local de invitación:
            <a href="{{ session('invitation_url') }}">{{ session('invitation_url') }}</a>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="demo-content-card">
        @if ($usuarios->isEmpty())
            <div class="demo-empty-state">
                <h2>No hay usuarios asociados</h2>
                <p>Agrega la primera persona que trabajará en este negocio.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table demo-crud-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            <tr>
                                <td><strong>{{ $usuario->name }}</strong></td>
                                <td>{{ $usuario->email }}</td>
                                <td>{{ ucfirst($usuario->pivot->rol) }}</td>
                                <td>
                                    <span class="badge {{ $usuario->pivot->activo ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $usuario->pivot->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>{{ $usuario->created_at->format('d-m-Y') }}</td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a href="{{ route('gestion.usuarios.edit', [$negocio, $usuario]) }}" class="btn btn-sm btn-demo-edit">
                                            Editar
                                        </a>
                                        <form action="{{ route('gestion.usuarios.destroy', [$negocio, $usuario]) }}" method="POST" onsubmit="return confirm('¿Quitar este usuario del negocio?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-demo-delete">Quitar</button>
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

    @if ($usuarios->hasPages())
        <div class="mt-4">{{ $usuarios->links() }}</div>
    @endif
</div>
@endsection
