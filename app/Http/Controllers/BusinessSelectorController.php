<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessSelectorController extends Controller
{
    public function index(Request $request): View
    {
        $negocios = $request
            ->user()
            ->negocios()
            ->wherePivot('activo', true)
            ->where('negocios.activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'account.business-select',
            compact('negocios')
        );
    }
}