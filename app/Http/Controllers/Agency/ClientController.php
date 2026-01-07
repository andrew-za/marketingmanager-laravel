<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request, $agencyId)
    {
        return view('agency.clients.index', compact('agencyId'));
    }
}

