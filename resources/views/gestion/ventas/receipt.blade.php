@extends('layouts.gestion')

@section('title', 'Boleta interna ' . $venta->numeroDocumentoParaMostrar() . ' | ' . $negocio->nombre)

@section('content')
    @php
        $logo = $negocio->logoUrl();
    @endphp

    <div class="container-fluid p-3 p-lg-5 receipt-page">
        <div class="receipt-actions d-flex flex-wrap justify-content-center gap-2 mb-4">
            <a href="{{ route('gestion.ventas.index', $negocio) }}"
                class="btn btn-outline-secondary">
                Volver a ventas
            </a>

            <button type="button" class="btn btn-acsoft-primary" onclick="window.print()">
                Imprimir
            </button>
        </div>

        <article class="sale-receipt mx-auto" aria-label="Boleta interna de venta">
            <header class="receipt-header text-center">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo de {{ $negocio->nombre }}" class="receipt-logo">
                @endif
                <h1>{{ $negocio->nombre }}</h1>
                <p class="receipt-kind">Boleta interna</p>
                <div class="receipt-number">N.º {{ $venta->numeroDocumentoParaMostrar() }}</div>
            </header>

            <section class="receipt-meta row g-2">
                <div class="col-6">
                    <span>Fecha</span>
                    <strong>{{ $venta->fecha->format('d/m/Y') }}</strong>
                </div>
                <div class="col-6 text-end">
                    <span>Hora</span>
                    <strong>{{ $venta->created_at?->format('H:i') ?? '—' }}</strong>
                </div>
                <div class="col-6">
                    <span>Usuario</span>
                    <strong>{{ $venta->usuario?->name ?? 'Usuario no disponible' }}</strong>
                </div>
                <div class="col-6 text-end">
                    <span>Forma de pago</span>
                    <strong>{{ $venta->metodo_pago ?: 'No especificada' }}</strong>
                </div>
            </section>

            <div class="table-responsive receipt-detail">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Precio unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($venta->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->descripcion }}</td>
                                <td class="text-end">{{ number_format((float) $detalle->cantidad, 3, ',', '.') }}</td>
                                <td class="text-end">${{ number_format((float) $detalle->precio_unitario, 0, ',', '.') }}</td>
                                <td class="text-end">${{ number_format((float) $detalle->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Sin detalle disponible</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="receipt-total d-flex justify-content-between align-items-center">
                <span>TOTAL</span>
                <strong>${{ number_format((float) $venta->total, 0, ',', '.') }}</strong>
            </div>

            <footer class="receipt-warning text-center">
                Documento interno. No válido como documento tributario ante el SII.
            </footer>
        </article>
    </div>
@endsection

@push('scripts')
    <style>
        .sale-receipt {
            max-width: 760px;
            background: #fff;
            border: 1px solid #dfe5ec;
            border-radius: 16px;
            box-shadow: 0 16px 40px rgba(15, 39, 68, .09);
            padding: clamp(1.25rem, 4vw, 2.5rem);
            color: #17263a;
        }
        .receipt-header { border-bottom: 2px solid var(--business-primary); padding-bottom: 1.25rem; }
        .receipt-logo { width: 150px; max-height: 58px; object-fit: contain; margin: 0 auto 1rem; }
        .receipt-header h1 { font-size: 1.45rem; margin-bottom: .25rem; }
        .receipt-kind { color: #526274; font-weight: 600; margin-bottom: .35rem; }
        .receipt-number { color: var(--business-primary); font-size: 1.1rem; font-weight: 800; }
        .receipt-meta { padding: 1.25rem 0; border-bottom: 1px dashed #aeb9c5; }
        .receipt-meta span { display: block; color: #687789; font-size: .78rem; text-transform: uppercase; }
        .receipt-meta strong { font-size: .95rem; }
        .receipt-detail { margin: 1.25rem 0; }
        .receipt-detail th { color: #526274; font-size: .78rem; text-transform: uppercase; white-space: nowrap; }
        .receipt-detail td { vertical-align: middle; }
        .receipt-total { border-top: 2px solid var(--business-primary); padding-top: 1rem; font-size: 1.25rem; }
        .receipt-total strong { font-size: 1.6rem; }
        .receipt-warning { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed #aeb9c5; font-size: .82rem; font-weight: 700; }

        @media (max-width: 575.98px) {
            .receipt-page { padding-left: .5rem !important; padding-right: .5rem !important; }
            .sale-receipt { border-radius: 8px; padding: 1rem; }
            .receipt-detail { font-size: .82rem; }
        }

        @media print {
            @page { margin: 6mm; }
            body, .gestion-app, .gestion-shell, .gestion-main { background: #fff !important; }
            .gestion-topbar, .gestion-sidebar, .receipt-actions { display: none !important; }
            .gestion-shell, .gestion-main { display: block !important; margin: 0 !important; padding: 0 !important; }
            .receipt-page { padding: 0 !important; }
            .sale-receipt {
                width: 100%;
                max-width: none;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }
            .receipt-header, .receipt-total { border-color: #000 !important; }
            .receipt-number { color: #000 !important; }
            .table-responsive { overflow: visible !important; }
            .receipt-detail table { font-size: 10pt; }
        }
    </style>
@endpush
