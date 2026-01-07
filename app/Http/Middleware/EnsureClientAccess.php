<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Agency;
use App\Models\Organization;

/**
 * Ensure agency member has access to specific client organization
 * Used in agency context to restrict access to assigned clients only
 */
class EnsureClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $agency = $request->route('agency');
        $organizationId = $request->route('organizationId');
        $user = $request->user();

        if (!$user) {
            abort(403, 'You must be authenticated to access this resource.');
        }

        // If agency is already resolved via route model binding, use it
        if (!$agency instanceof Agency) {
            $agencyId = $request->route('agencyId') ?? $agency;
            $agency = Agency::find($agencyId);
        }
        
        if (!$agency) {
            abort(404, 'Agency not found.');
        }

        // Agency admins have access to all clients
        if ($user->hasRole('agency-admin', $agency) || $user->hasRole('admin', $agency)) {
            return $next($request);
        }

        // Check if organizationId is provided
        if (!$organizationId) {
            return $next($request);
        }

        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            abort(404, 'Organization not found.');
        }

        // Check if agency has this client assigned
        $hasClient = $agency->clients()->where('organizations.id', $organizationId)->exists();
        
        if (!$hasClient) {
            abort(403, 'You do not have access to this client organization.');
        }

        // Check if user has access to this organization through agency
        if (!$user->isAgencyMember($agency->id)) {
            abort(403, 'You do not have access to this agency.');
        }

        return $next($request);
    }
}

