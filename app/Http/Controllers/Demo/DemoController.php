<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DemoController extends Controller
{
    public function index(): View
    {
        return view('demo.dashboard');
    }
}