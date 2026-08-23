@extends('layouts.app')

@section('title', 'ACSoft | Sistemas web simples para pequeños negocios')

@section('description',
    'Digitaliza procesos de tu negocio con sistemas web simples, claros y adaptados a tu forma de
    trabajar. Prueba nuestra demo de recepción de mercadería.')

@section('og_title', 'ACSoft | Software simple para negocios reales')

@section('og_description',
    'Transforma cuadernos, planillas y procesos manuales en sistemas web simples para tu
    negocio.')

@section('canonical', route('home'))

@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'ACSoft',
        'url' => route('home'),
        'logo' => asset('images/acsoft/logo.svg'),
        'description' => 'Desarrollo de sistemas web simples y personalizados para pequeños negocios.',
    ];

    if (config('acsoft.email')) {
        $structuredData['email'] = config('acsoft.email');
    }
@endphp

@push('head')
    <script type="application/ld+json">
        {!! json_encode(
            $structuredData,
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_PRETTY_PRINT
        ) !!}
    </script>
@endpush

@section('content')

    <section class="hero-acsoft">

        <div class="container">

            <div class="row align-items-center min-vh-75 g-5">

                <div class="col-lg-6">

                    <div class="hero-badge mb-4">
                        <span class="hero-badge-dot"></span>
                        Sistemas web para pequeños negocios
                    </div>

                    <h1 class="hero-title">
                        Ordena tu negocio con
                        <span>herramientas simples</span>
                        y hechas para trabajar.
                    </h1>

                    <p class="hero-description">
                        Digitaliza procesos que hoy llevas en cuadernos,
                        planillas o mensajes de WhatsApp con sistemas web
                        claros, rápidos y adaptados a tu negocio.
                    </p>

                    <div class="d-flex flex-column flex-sm-row gap-3 mt-4">

                        <a href="{{ route('demo.index') }}" class="btn btn-acsoft-primary btn-lg px-4">
                            Probar demo
                        </a>

                        <x-whatsapp-button label="Hablar por WhatsApp" class="btn btn-acsoft-outline btn-lg px-4"
                            message="Hola, vi ACSoft y quisiera conocer más sobre sus sistemas para negocios." />

                    </div>

                    <div class="hero-benefits">

                        <div>
                            <span>✓</span>
                            Funciona desde celular y computador
                        </div>

                        <div>
                            <span>✓</span>
                            Sin instalaciones complicadas
                        </div>

                        <div>
                            <span>✓</span>
                            Adaptable a diferentes negocios
                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="hero-visual">

                        <div class="hero-dashboard">

                            <div class="dashboard-header">

                                <div>
                                    <small>ACSoft</small>
                                    <h5>Recepción de mercadería</h5>
                                </div>

                                <span class="dashboard-status">
                                    Activo
                                </span>

                            </div>

                            <div class="dashboard-body">

                                <div class="dashboard-fields">

                                    <div class="fake-field">
                                        <span>Proveedor</span>
                                        <strong>Distribuidora Sur</strong>
                                    </div>

                                    <div class="fake-field">
                                        <span>Producto</span>
                                        <strong>Producto ejemplo</strong>
                                    </div>

                                </div>

                                <div class="weight-list">

                                    <div class="weight-row">
                                        <span>Caja 01</span>
                                        <strong>18,42 kg</strong>
                                    </div>

                                    <div class="weight-row">
                                        <span>Caja 02</span>
                                        <strong>19,10 kg</strong>
                                    </div>

                                    <div class="weight-row">
                                        <span>Caja 03</span>
                                        <strong>17,86 kg</strong>
                                    </div>

                                </div>

                                <div class="dashboard-total">

                                    <span>Total recibido</span>

                                    <strong>
                                        55,38 kg
                                    </strong>

                                </div>

                            </div>

                        </div>

                        <div class="floating-card floating-card-top">
                            <span class="floating-icon">
                                ✓
                            </span>

                            <div>
                                <small>Registro guardado</small>
                                <strong>3 cajas</strong>
                            </div>
                        </div>

                        <div class="floating-card floating-card-bottom">

                            <div>
                                <small>Total automático</small>
                                <strong>55,38 kg</strong>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
    @include('home.sections.problem')

    @include('home.sections.solution')

    @include('home.sections.how-it-works')

    @include('home.sections.features')

    @include('home.sections.demo')

    @include('home.sections.benefits')

    @include('home.sections.industries')

    @include('home.sections.services')

    @include('home.sections.contact')

    @include('home.sections.final-cta')
@endsection
