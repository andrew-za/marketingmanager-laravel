<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;

/**
 * Ensure user is not a Client role
 * Protects routes that should be hidden from Client role (Campaigns, Projects, Tasks, etc.)
 */
class EnsureNotClientRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->route('organizationId');
        $user = $request->user();

        if (!$user) {
            abort(403, 'You must be authenticated to access this resource.');
        }

        // Platform admins always have access
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$organizationId) {
            abort(400, 'Organization ID is required.');
        }

        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            abort(404, 'Organization not found.');
        }

        // Check if user has client or viewer role
        $isClientRole = $user->hasRole('client', $organization) 
            || $user->hasRole('viewer', $organization);
        
        if ($isClientRole) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}

