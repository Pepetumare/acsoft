<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(
        Request $request,
        Negocio $negocio
    ): View {

        $datos = $this->generarDatosReporte(
            $request,
            $negocio
        );

        return view(
            'gestion.reportes.index',
            $datos
        );
    }


    public function pdf(
        Request $request,
        Negocio $negocio
    ) {

        $datos = $this->generarDatosReporte(
            $request,
            $negocio
        );

        $pdf = Pdf::loadView(
            'gestion.reportes.pdf',
            $datos
        );

        $pdf->setPaper(
            'a4',
            'portrait'
        );

        return $pdf->download(
            'reporte-'
                . $negocio->slug
                . '-'
                . $datos['desde']->format('Y-m-d')
                . '-'
                . $datos['hasta']->format('Y-m-d')
                . '.pdf'
        );
    }


    private function generarDatosReporte(
        Request $request,
        Negocio $negocio
    ): array {

        $validated = $request->validate([
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'desde.date_format' => 'La fecha desde debe tener el formato AAAA-MM-DD.',
            'hasta.date_format' => 'La fecha hasta debe tener el formato AAAA-MM-DD.',
        ]);

        $desde = isset($validated['desde'])
            ? Carbon::createFromFormat('Y-m-d', $validated['desde'])->startOfDay()
            : now()->startOfMonth();

        $hasta = isset($validated['hasta'])
            ? Carbon::createFromFormat('Y-m-d', $validated['hasta'])->endOfDay()
            : now();

        if ($desde->gt($hasta)) {
            throw ValidationException::withMessages([
                'hasta' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
            ]);
        }

        if ($desde->diffInDays($hasta) > 366) {
            throw ValidationException::withMessages([
                'hasta' => 'El período del reporte no puede superar 366 días.',
            ]);
        }


        $ventasQuery = $negocio
            ->ventas()
            ->whereBetween('fecha', [
                $desde->toDateString(),
                $hasta->toDateString(),
            ]);


        $gastosQuery = $negocio
            ->gastos()
            ->whereBetween('fecha', [
                $desde->toDateString(),
                $hasta->toDateString(),
            ]);


        $resumenVentas = (clone $ventasQuery)
            ->selectRaw('COALESCE(SUM(total), 0) as total_ventas')
            ->selectRaw('COUNT(*) as cantidad_ventas')
            ->first();

        $totalVentas = (float) $resumenVentas->total_ventas;
        $cantidadVentas = (int) $resumenVentas->cantidad_ventas;


        $ticketPromedio = $cantidadVentas > 0
            ? $totalVentas / $cantidadVentas
            : 0;


        $totalGastos = (float) (clone $gastosQuery)
            ->sum('monto');


        $resultado = $totalVentas - $totalGastos;


        $ventasPorMetodo = (clone $ventasQuery)
            ->selectRaw("
                COALESCE(
                    metodo_pago,
                    'Sin especificar'
                ) as metodo,
                SUM(total) as total
            ")
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();


        $gastosPorCategoria = (clone $gastosQuery)
            ->selectRaw("
                COALESCE(
                    categoria,
                    'Sin categoría'
                ) as categoria_nombre,
                SUM(monto) as total
            ")
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();


        return [
            'negocio' => $negocio,
            'desde' => $desde,
            'hasta' => $hasta,
            'totalVentas' => $totalVentas,
            'totalGastos' => $totalGastos,
            'resultado' => $resultado,
            'cantidadVentas' => $cantidadVentas,
            'ticketPromedio' => $ticketPromedio,
            'ventasPorMetodo' => $ventasPorMetodo,
            'gastosPorCategoria' => $gastosPorCategoria,
        ];
    }
}
