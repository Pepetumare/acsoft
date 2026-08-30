<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Negocio $negocio
    ): View {


        $negocio->load('modulosActivos');

        return view(
            'gestion.dashboard',
            compact('negocio')
        );
    }
}
