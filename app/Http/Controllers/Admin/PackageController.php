<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        return view('admin.packages.index');
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement package creation
        return redirect()->route('admin.packages.index');
    }

    public function show($id)
    {
        return view('admin.packages.show', compact('id'));
    }

    public function edit($id)
    {
        return view('admin.packages.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement package update
        return redirect()->route('admin.packages.index');
    }

    public function destroy($id)
    {
        // TODO: Implement package deletion
        return redirect()->route('admin.packages.index');
    }
}

