<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Support\BusinessAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function landing(Request $request, Negocio $negocio): View|RedirectResponse
    {
        $route = $negocio->landingRouteNameFor($request->user());

        return $route
            ? redirect()->route($route, $negocio)
            : view('gestion.no-modules', compact('negocio'));
    }

    public function index(Request $request, Negocio $negocio, BusinessAnalytics $analytics): View|RedirectResponse
    {
        $periodo = $request->string('periodo')->toString() ?: 'ultimos_30_dias';
        $allowedPeriods = ['hoy', 'ultimos_7_dias', 'ultimos_30_dias', 'este_mes', 'mes_anterior', 'personalizado'];
        $validator = Validator::make($request->query(), [
            'periodo' => ['nullable', 'in:'.implode(',', $allowedPeriods)],
            'desde' => ['nullable', 'required_if:periodo,personalizado', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'required_if:periodo,personalizado', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ], [
            'periodo.in' => 'Selecciona un período válido.',
            'desde.required_if' => 'Indica la fecha desde para el rango personalizado.',
            'hasta.required_if' => 'Indica la fecha hasta para el rango personalizado.',
            'desde.date_format' => 'La fecha desde debe tener el formato AAAA-MM-DD.',
            'hasta.date_format' => 'La fecha hasta debe tener el formato AAAA-MM-DD.',
            'hasta.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('gestion.analitica', $negocio)->withErrors($validator)->withInput($request->query());
        }

        [$desde, $hasta] = $this->datesFor($periodo, $request);
        if ($desde->diffInDays($hasta) > 366) {
            return redirect()->route('gestion.analitica', $negocio)
                ->withErrors(['hasta' => 'El período de analítica no puede superar 366 días.'])
                ->withInput($request->query());
        }

        $negocio->load('modulosActivos');

        return view('gestion.dashboard', array_merge(
            compact('negocio', 'periodo', 'desde', 'hasta'),
            $analytics->for($negocio, $desde, $hasta)
        ));
    }

    /** @return array{CarbonImmutable, CarbonImmutable} */
    private function datesFor(string $periodo, Request $request): array
    {
        $today = CarbonImmutable::today();

        return match ($periodo) {
            'hoy' => [$today, $today],
            'ultimos_7_dias' => [$today->subDays(6), $today],
            'este_mes' => [$today->startOfMonth(), $today],
            'mes_anterior' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'personalizado' => [
                CarbonImmutable::createFromFormat('Y-m-d', $request->string('desde')->toString())->startOfDay(),
                CarbonImmutable::createFromFormat('Y-m-d', $request->string('hasta')->toString())->startOfDay(),
            ],
            default => [$today->subDays(29), $today],
        };
    }
}
