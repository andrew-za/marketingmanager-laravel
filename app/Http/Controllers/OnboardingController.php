<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        return view('onboarding.index');
    }
}

