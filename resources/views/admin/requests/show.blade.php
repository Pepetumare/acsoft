@extends('layouts.admin')
@section('title', 'Solicitud de ' . $contactRequest->name . ' | ACSoft')
@section('content')
<div class="container py-5">
    <a href="{{ route('admin.solicitudes.index') }}" class="demo-back-link">← Volver a solicitudes</a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mt-3 mb-4"><div><span class="section-eyebrow">Solicitud #{{ $contactRequest->id }}</span><h1 class="mt-2 mb-1">{{ $contactRequest->name }}</h1><p class="text-muted mb-0">Recibida el {{ $contactRequest->created_at->format('d/m/Y \a \l\a\s H:i') }}</p></div><span class="request-status request-status-{{ $contactRequest->statusStyleValue() }}">{{ $contactRequest->statusLabel() }}</span></div>

    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row g-4"><div class="col-lg-8"><section class="admin-request-detail"><h2>Datos enviados</h2><dl class="request-data-grid"><div><dt>Nombre</dt><dd>{{ $contactRequest->name }}</dd></div><div><dt>Tipo</dt><dd>{{ $contactRequest->typeLabel() }}</dd></div><div><dt>Correo</dt><dd><a href="mailto:{{ $contactRequest->email }}">{{ $contactRequest->email ?: $contactRequest->contact }}</a></dd></div><div><dt>Teléfono</dt><dd>{{ $contactRequest->phone ?: '—' }}</dd></div><div><dt>Negocio o empresa</dt><dd>{{ $contactRequest->business ?: '—' }}</dd></div><div><dt>Última actualización</dt><dd>{{ $contactRequest->updated_at->format('d/m/Y H:i') }}</dd></div></dl><div class="request-message"><span>Mensaje</span><p>{{ $contactRequest->message }}</p></div></section></div>
        <div class="col-lg-4"><section class="admin-request-detail"><h2>Cambiar estado</h2><p class="text-muted small">Actualiza el avance comercial de esta solicitud.</p><form method="POST" action="{{ route('admin.solicitudes.update', $contactRequest) }}">@csrf @method('PATCH')<label for="status" class="form-label">Estado actual</label><select id="status" name="status" class="form-select mb-3" required>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($contactRequest->statusEnum() === $status)>{{ $status->label() }}</option>@endforeach</select><button class="btn btn-acsoft-primary w-100">Guardar estado</button></form></section></div>
    </div>
</div>
@endsection
