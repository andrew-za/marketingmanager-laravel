<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

/**
 * Ensure user is an organization admin
 * Protects routes that require organization admin privileges
 */
class EnsureOrganizationAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->route('organizationId');
        $user = $request->user();

        if (!$user) {
            abort(403, 'You must be authenticated to access this resource.');
        }

        if (!$organizationId) {
            abort(400, 'Organization ID is required.');
        }

        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            abort(404, 'Organization not found.');
        }

        // Check if user has admin role in organization
        $isOrgAdmin = $user->hasRole('admin', $organization) 
            || $user->hasRole('super-admin', $organization);
        
        if (!$isOrgAdmin && !$user->isAdmin()) {
            abort(403, 'You do not have permission to access this resource. Organization admin access required.');
        }

        return $next($request);
    }
}

