<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Redirect to clients page (default route)
     */
    public function index(Request $request, Agency $agency)
    {
        return redirect()->route('agency.clients.index', ['agency' => $agency]);
    }
}


