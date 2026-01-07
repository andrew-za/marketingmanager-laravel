<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Handles public marketing site pages
 */
class PublicController extends Controller
{
    /**
     * Show the homepage/landing page
     */
    public function index(): View
    {
        return view('public.home');
    }

    /**
     * Show the features page
     */
    public function features(): View
    {
        return view('public.features');
    }

    /**
     * Show the pricing page
     */
    public function pricing(): View
    {
        return view('public.pricing');
    }

    /**
     * Show the about page
     */
    public function about(): View
    {
        return view('public.about');
    }

    /**
     * Show the contact page
     */
    public function contact(): View
    {
        return view('public.contact');
    }
}

