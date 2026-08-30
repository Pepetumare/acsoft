@extends('layouts.admin')
@section('title', 'Solicitudes | ACSoft Administración')
@section('content')
<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4"><div><span class="section-eyebrow">ACSoft Administración</span><h1 class="mt-2 mb-1">Solicitudes</h1><p class="text-muted mb-0">Revisa y gestiona los contactos recibidos desde el sitio público.</p></div><span class="badge text-bg-light border">{{ $requests->total() }} resultados</span></div>

    <form method="GET" action="{{ route('admin.solicitudes.index') }}" class="admin-request-filters mb-4">
        <div><label for="search" class="form-label">Buscar</label><input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nombre, correo o negocio"></div>
        <div><label for="status" class="form-label">Estado</label><select id="status" name="status" class="form-select"><option value="">Todos</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
        <div><label for="date" class="form-label">Fecha</label><input id="date" type="date" name="date" class="form-control" value="{{ request('date') }}"></div>
        <div class="d-flex gap-2 align-self-end"><button class="btn btn-acsoft-primary">Filtrar</button><a href="{{ route('admin.solicitudes.index') }}" class="btn btn-acsoft-outline">Limpiar</a></div>
    </form>

    <div class="demo-content-card">
        @if ($requests->isEmpty())
            <div class="demo-empty-state"><div class="demo-empty-icon">S</div><h2>No se encontraron solicitudes</h2><p>Prueba cambiando los filtros de búsqueda.</p></div>
        @else
            <div class="demo-table-responsive"><table class="table demo-crud-table align-middle mb-0"><thead><tr><th>Fecha</th><th>Nombre</th><th>Correo</th><th>Negocio</th><th>Tipo</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
            @foreach ($requests as $contactRequest)
                <tr><td><span class="text-nowrap">{{ $contactRequest->created_at->format('d/m/Y') }}</span><small class="d-block text-muted">{{ $contactRequest->created_at->format('H:i') }}</small></td><td><strong>{{ $contactRequest->name }}</strong></td><td><a href="mailto:{{ $contactRequest->email }}">{{ $contactRequest->email ?: $contactRequest->contact }}</a></td><td>{{ $contactRequest->business ?: '—' }}</td><td><span class="request-type-badge">{{ $contactRequest->typeLabel() }}</span></td><td><span class="request-status request-status-{{ $contactRequest->statusStyleValue() }}">{{ $contactRequest->statusLabel() }}</span></td><td class="text-end"><a href="{{ route('admin.solicitudes.show', $contactRequest) }}" class="btn btn-sm btn-demo-edit">Ver detalle</a></td></tr>
            @endforeach
            </tbody></table></div>
        @endif
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
</div>
@endsection
