@extends('layouts.app')
@section('title', 'Contacto | ACSoft')
@section('description', 'Solicita una demostración de ACSoft o cuéntanos qué necesita tu negocio.')
@section('canonical', route('contacto'))
@section('content')
<header class="public-page-header public-page-header-compact"><div class="container text-center"><span class="section-eyebrow">Contacto</span><h1>Conversemos sobre tu negocio</h1><p>Solicita una demostración o envíanos tu consulta. Te responderemos para coordinar los siguientes pasos.</p></div></header>
@include('home.sections.contact')
@endsection
