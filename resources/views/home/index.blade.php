@extends('layouts.app')
@section('title', 'ACSoft | Gestión simple para tu negocio')
@section('description', 'Controla ventas, caja, gastos, productos, stock y compras con ACSoft, un sistema web claro y fácil de usar.')
@section('canonical', route('home'))

@section('content')
<section class="hero-acsoft public-hero">
    <div class="container"><div class="row align-items-center g-4 g-xl-5">
        <div class="col-lg-6">
            <div class="hero-badge mb-3"><span class="hero-badge-dot"></span> Gestión simple para negocios reales</div>
            <h1 class="hero-title">Tu negocio en orden, <span>sin complicaciones.</span></h1>
            <p class="hero-description">Controla ventas, caja, gastos, productos, stock y compras desde un sistema web claro, pensado para el trabajo de todos los días.</p>
            <div class="d-flex flex-column flex-sm-row gap-3 mt-4"><a href="{{ route('contacto') }}" class="btn btn-acsoft-primary btn-lg px-4">Solicitar demostración</a><a href="{{ route('functions') }}" class="btn btn-acsoft-outline btn-lg px-4">Ver funciones</a></div>
            <div class="demo-duration"><span>✓</span><strong>Demostración por 5 días</strong><small>con usuario temporal y negocio de prueba</small></div>
        </div>
        <div class="col-lg-6">
            <div class="product-carousel-shell">
                <div class="product-window-bar"><span></span><span></span><span></span><small>app.acsoft.cl</small></div>
                <div id="acsoftProductCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
                    <div class="carousel-indicators"><button type="button" data-bs-target="#acsoftProductCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Resumen"></button><button type="button" data-bs-target="#acsoftProductCarousel" data-bs-slide-to="1" aria-label="Ventas"></button><button type="button" data-bs-target="#acsoftProductCarousel" data-bs-slide-to="2" aria-label="Stock"></button></div>
                    <div class="carousel-inner">
                        <div class="carousel-item active"><div class="product-slide"><div class="product-slide-heading"><span>Panel general</span><strong>Resumen del negocio</strong></div><div class="public-stat-grid"><div class="public-stat"><span>Ventas de hoy</span><strong>24</strong></div><div class="public-stat"><span>Caja</span><strong class="text-success">Abierta</strong></div><div class="public-stat"><span>Stock bajo</span><strong>3 productos</strong></div><div class="public-stat"><span>Compras del mes</span><strong>12</strong></div></div></div></div>
                        <div class="carousel-item"><div class="product-slide"><div class="product-slide-heading"><span>Ventas</span><strong>Operación del día</strong></div><div class="product-table"><div><span>Venta #0241</span><strong>$ 18.500</strong></div><div><span>Venta #0240</span><strong>$ 32.990</strong></div><div><span>Venta #0239</span><strong>$ 9.800</strong></div></div><div class="product-slide-total"><span>Total registrado</span><strong>$ 61.290</strong></div></div></div>
                        <div class="carousel-item"><div class="product-slide"><div class="product-slide-heading"><span>Inventario</span><strong>Stock de productos</strong></div><div class="product-table"><div><span>Producto A</span><em class="stock-ok">Disponible</em><strong>28 un.</strong></div><div><span>Producto B</span><em class="stock-low">Stock bajo</em><strong>3 un.</strong></div><div><span>Producto C</span><em class="stock-ok">Disponible</em><strong>16 un.</strong></div></div></div></div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#acsoftProductCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span></button><button class="carousel-control-next" type="button" data-bs-target="#acsoftProductCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span></button>
                </div>
            </div>
        </div>
    </div></div>
</section>

<section class="public-section bg-white"><div class="container">
    <div class="text-center public-heading"><span class="section-eyebrow">Lo esencial</span><h2 class="section-title mx-auto mt-2">Más claridad para tomar mejores decisiones</h2></div>
    <div class="row g-3">
        @foreach ([['central', 'Todo en un lugar', 'Consulta la información de tu negocio cuando la necesites.'], ['simple', 'Simple para tu equipo', 'Registra las tareas diarias sin procesos innecesarios.'], ['insights', 'Información confiable', 'Ventas, caja y stock conectados en una visión real.']] as [$icon, $title, $text])
        <div class="col-md-4"><article class="public-card h-100"><span class="public-card-icon"><x-product-icon :name="$icon" /></span><div><h3>{{ $title }}</h3><p>{{ $text }}</p></div></article></div>
        @endforeach
    </div>
</div></section>

<section class="public-section"><div class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 public-heading"><div><span class="section-eyebrow">Módulos principales</span><h2 class="section-title mt-2 mb-0">Las herramientas que tu negocio necesita</h2></div><a href="{{ route('functions') }}" class="public-text-link">Conocer todas las funciones →</a></div>
    <div class="public-module-grid">
        @foreach ([['sales', 'Ventas', 'Registra cada venta y mantén su detalle.'], ['cash', 'Caja', 'Controla aperturas, cierres y movimientos.'], ['expenses', 'Gastos', 'Ordena los egresos diarios del negocio.'], ['products', 'Productos', 'Administra tu catálogo de forma simple.'], ['stock', 'Stock', 'Conoce existencias y movimientos.'], ['purchases', 'Compras', 'Registra ingresos y costos de mercadería.']] as [$icon, $title, $text])
        <article class="public-module"><span class="module-icon"><x-product-icon :name="$icon" /></span><div><h3>{{ $title }}</h3><p>{{ $text }}</p></div></article>
        @endforeach
    </div>
</div></section>

<section class="how-compact bg-white"><div class="container"><div class="row align-items-center g-4"><div class="col-lg-4"><span class="section-eyebrow">Cómo funciona</span><h2 class="section-title mt-2 mb-2">De la configuración al control</h2><p class="section-description mb-0">Te acompañamos para que comiences sin complicaciones.</p></div><div class="col-lg-8"><div class="how-steps">
    @foreach ([['01', 'Configuramos tu negocio', 'Preparamos usuarios, módulos y datos iniciales.'], ['02', 'Comienzas a registrar', 'Tu equipo usa ACSoft en la operación diaria.'], ['03', 'Revisas tu operación', 'Consultas ventas, caja y stock en un solo lugar.']] as [$number, $title, $text])
    <article><span>{{ $number }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></article>
    @endforeach
</div></div></div></div></section>

<section class="public-section"><div class="container"><div class="public-audience"><div><span class="section-eyebrow">Para quién sirve</span><h2 class="section-title mt-2">Hecho para negocios que necesitan orden y control</h2><p class="section-description">Para equipos que venden productos, manejan caja y necesitan conocer su inventario sin depender de registros dispersos.</p></div><div class="audience-grid">
    @foreach ([['store', 'Minimarkets y almacenes'], ['inventory', 'Carnicerías y pollerías'], ['truck', 'Distribuidoras'], ['shop', 'Tiendas minoristas'], ['food', 'Restaurantes pequeños'], ['products', 'Negocios con inventario']] as [$icon, $label])
    <div><span><x-product-icon :name="$icon" /></span>{{ $label }}</div>
    @endforeach
</div></div></div></section>

@include('home.sections.final-cta')
@endsection
