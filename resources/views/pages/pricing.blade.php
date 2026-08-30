@extends('layouts.app')
@section('title', 'Precios | ACSoft')
@section('description', 'Conoce la estructura de planes de ACSoft y solicita una propuesta para tu negocio.')
@section('canonical', route('pricing'))
@section('content')
@php
// Contenido comercial editable. Agregar valores aquí cuando estén definidos.
$plans = [
    ['name' => 'Esencial', 'eyebrow' => 'Para comenzar', 'description' => 'Ordena la operación diaria con las herramientas principales.', 'modules' => ['Ventas', 'Caja', 'Productos'], 'extras' => ['Acceso desde celular y computador', 'Soporte de puesta en marcha'], 'cta' => 'Consultar plan'],
    ['name' => 'Gestión', 'eyebrow' => 'Mayor control', 'description' => 'Conecta la operación completa y revisa el negocio con claridad.', 'modules' => ['Ventas', 'Caja', 'Gastos', 'Productos', 'Stock', 'Compras'], 'extras' => ['Historial y reportes', 'Acompañamiento y soporte'], 'cta' => 'Solicitar demostración', 'featured' => true],
    ['name' => 'A medida', 'eyebrow' => 'Necesidades especiales', 'description' => 'Una propuesta basada en procesos o requerimientos particulares.', 'modules' => ['Módulos según alcance', 'Configuración personalizada'], 'extras' => ['Evaluación del proceso', 'Propuesta según alcance'], 'cta' => 'Cotizar solución'],
];
@endphp
<header class="public-page-header"><div class="container text-center"><span class="section-eyebrow">Precios</span><h1>Un plan acorde a tu operación</h1><p>Los valores comerciales aún están por definir. Cuéntanos qué necesita tu negocio y te entregaremos una propuesta clara.</p></div></header>
<section class="public-section bg-white"><div class="container"><div class="row g-3 justify-content-center">
@foreach ($plans as $plan)
<div class="col-lg-4"><article class="pricing-card h-100 {{ !empty($plan['featured']) ? 'pricing-card-featured' : '' }}">@if (!empty($plan['featured']))<span class="pricing-badge">Recomendado</span>@endif<small class="pricing-eyebrow">{{ $plan['eyebrow'] }}</small><h2>{{ $plan['name'] }}</h2><p>{{ $plan['description'] }}</p><div class="pricing-value"><strong>Valor por definir</strong><span>Propuesta según operación</span></div><div class="pricing-modules"><span>Módulos incluidos</span><div>@foreach ($plan['modules'] as $module)<em>{{ $module }}</em>@endforeach</div></div><ul>@foreach ($plan['extras'] as $feature)<li>{{ $feature }}</li>@endforeach</ul><a href="{{ route('contact') }}" class="btn {{ !empty($plan['featured']) ? 'btn-acsoft-primary' : 'btn-acsoft-outline' }} w-100">{{ $plan['cta'] }}</a></article></div>
@endforeach
</div><p class="pricing-note text-center">Los módulos, condiciones y soporte pueden ajustarse según las necesidades de cada negocio.</p></div></section>
@endsection
