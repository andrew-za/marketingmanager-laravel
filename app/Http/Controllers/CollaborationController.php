<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CollaborationController extends Controller
{
    public function index(Request $request)
    {
        return view('collaboration.index');
    }
}

