<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, $agencyId)
    {
        return view('agency.dashboard.index', compact('agencyId'));
    }
}

