<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Workflow;
use Illuminate\Http\Request;

/**
 * Organization Automation Controller
 * Handles automation workflow management
 * Requires organization admin access
 */
class AutomationController extends Controller
{
    /**
     * Display automation workflows
     */
    public function index(Request $request, Organization $organization)
    {
        $workflows = Workflow::where('organization_id', $organization->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('organization.automations.index', [
            'organization' => $organization,
            'workflows' => $workflows,
        ]);
    }
}

