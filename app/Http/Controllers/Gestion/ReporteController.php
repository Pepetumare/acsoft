<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))
            : now()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))
            : now();


        $ventasQuery = $negocio
            ->ventas()
            ->whereDate(
                'fecha',
                '>=',
                $desde->toDateString()
            )
            ->whereDate(
                'fecha',
                '<=',
                $hasta->toDateString()
            );


        $gastosQuery = $negocio
            ->gastos()
            ->whereDate(
                'fecha',
                '>=',
                $desde->toDateString()
            )
            ->whereDate(
                'fecha',
                '<=',
                $hasta->toDateString()
            );


        $totalVentas = (float) (clone $ventasQuery)
            ->sum('total');


        $cantidadVentas = (clone $ventasQuery)
            ->count();


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
