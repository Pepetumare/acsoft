@extends('layouts.app')

@section('title', 'Invitación a negocio | ACSoft')

@section('content')
<div class="container py-5" style="max-width: 680px">
    <div class="contact-form-card">
        <h1 class="h3">Invitación a {{ $invitation->negocio->nombre }}</h1>
        <p>Confirma que deseas incorporarte con el rol {{ $invitation->rol }}.</p>
        <form method="POST" action="{{ route('business-invitations.accept', $token) }}">
            @csrf
            <button type="submit" class="btn btn-acsoft-primary">Aceptar invitación</button>
        </form>
    </div>
</div>
@endsection
