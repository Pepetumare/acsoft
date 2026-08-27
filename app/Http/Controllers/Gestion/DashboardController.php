<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        Negocio $negocio
    ): View {

        $user = $request->user();

        if (!$user->is_superadmin) {

            $tieneAcceso = $user
                ->negocios()
                ->where('negocios.id', $negocio->id)
                ->wherePivot('activo', true)
                ->exists();

            abort_unless(
                $tieneAcceso && $negocio->activo,
                403
            );
        }

        $negocio->load('modulosActivos');

        return view(
            'gestion.dashboard',
            compact('negocio')
        );
    }
}
