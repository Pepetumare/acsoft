<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\View\View;

class ModuloPlaceholderController extends Controller
{
    public function ventas(Negocio $negocio): View
    {
        return view(
            'gestion.modulos.placeholder',
            [
                'negocio' => $negocio,
                'titulo' => 'Ventas',
            ]
        );
    }

    public function gastos(Negocio $negocio): View
    {
        return view(
            'gestion.modulos.placeholder',
            [
                'negocio' => $negocio,
                'titulo' => 'Gastos',
            ]
        );
    }

    public function reportes(Negocio $negocio): View
    {
        return view(
            'gestion.modulos.placeholder',
            [
                'negocio' => $negocio,
                'titulo' => 'Reportes',
            ]
        );
    }
}