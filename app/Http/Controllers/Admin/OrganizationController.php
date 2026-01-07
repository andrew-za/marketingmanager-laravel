<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::paginate();
        return view('admin.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization)
    {
        return view('admin.organizations.show', compact('organization'));
    }
}


