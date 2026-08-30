@extends('layouts.app')
@section('title', 'Funciones | ACSoft')
@section('description', 'Conoce los módulos de ventas, caja, gastos, productos, stock y compras de ACSoft.')
@section('canonical', route('functions'))
@section('content')
@php
$modules = [
    ['sales', 'Ventas', 'Registra ventas con sus productos y cantidades. El stock se actualiza para mantener la información consistente.', ['Detalle de cada operación', 'Historial de ventas', 'Actualización de stock']],
    ['cash', 'Caja', 'Abre y cierra caja, registra movimientos y compara el saldo esperado con el dinero disponible.', ['Apertura y cierre', 'Ingresos y egresos', 'Historial de cajas']],
    ['expenses', 'Gastos', 'Registra los gastos del día y consúltalos junto al resto de la operación.', ['Registro ordenado', 'Concepto y monto', 'Consulta histórica']],
    ['products', 'Productos', 'Mantén un catálogo con la información comercial necesaria para vender y controlar inventario.', ['Catálogo centralizado', 'Precios y datos', 'Edición simple']],
    ['stock', 'Stock', 'Consulta existencias y revisa los movimientos que explican cada cambio de inventario.', ['Stock actual', 'Historial de movimientos', 'Trazabilidad']],
    ['purchases', 'Compras', 'Registra compras de mercadería para mantener al día costos y existencias.', ['Detalle de productos', 'Ingreso de existencias', 'Historial de compras']],
];
@endphp
<header class="public-page-header"><div class="container text-center"><span class="section-eyebrow">Funciones</span><h1>Lo necesario para gestionar tu negocio</h1><p>Seis módulos conectados, una experiencia simple y la información disponible cuando la necesitas.</p></div></header>
<section class="public-section bg-white"><div class="container"><div class="function-grid">
@foreach ($modules as [$icon, $title, $text, $items])
<article class="function-card"><div class="function-card-head"><span class="function-icon"><x-product-icon :name="$icon" /></span><div><small>Módulo ACSoft</small><h2>{{ $title }}</h2></div></div><p>{{ $text }}</p><ul>@foreach ($items as $item)<li>{{ $item }}</li>@endforeach</ul></article>
@endforeach
</div></div></section>

<section class="product-showcase"><div class="container"><div class="text-center public-heading"><span class="section-eyebrow">El producto en acción</span><h2 class="section-title mx-auto mt-2">Una interfaz clara para el trabajo diario</h2></div><div class="showcase-grid">
    <article class="showcase-card showcase-card-wide"><div><span>01</span><h3>Ventas y caja conectadas</h3><p>Registra la operación y mantén el control del dinero del día.</p></div><div class="shot-placeholder"><span>Vista de ventas</span><div class="shot-lines"><i></i><i></i><i></i></div></div></article>
    <article class="showcase-card"><div><span>02</span><h3>Stock visible</h3><p>Consulta existencias y movimientos con rapidez.</p></div><div class="shot-placeholder"><span>Vista de stock</span><div class="shot-bars"><i></i><i></i><i></i></div></div></article>
    <article class="showcase-card"><div><span>03</span><h3>Operación resumida</h3><p>Revisa los datos importantes en un solo panel.</p></div><div class="shot-placeholder"><span>Panel general</span><div class="shot-metrics"><i></i><i></i><i></i></div></div></article>
</div></div></section>
<section class="public-inline-cta"><div class="container text-center"><span class="demo-pill">Demostración por 5 días</span><h2>¿Quieres verlo aplicado a tu negocio?</h2><p>Prepararemos un usuario temporal con un negocio de prueba.</p><a href="{{ route('contact') }}" class="btn btn-acsoft-primary btn-lg">Solicitar demostración</a></div></section>
@endsection
