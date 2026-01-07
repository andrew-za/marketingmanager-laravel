<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Agency;

/**
 * Ensure user is an agency admin
 * Protects routes that require agency admin privileges
 */
class EnsureAgencyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $agency = $request->route('agency');
        $user = $request->user();

        if (!$user) {
            abort(403, 'You must be authenticated to access this resource.');
        }

        // If agency is already resolved via route model binding, use it
        if (!$agency instanceof Agency) {
            // Fallback for agencyId parameter
            $agencyId = $request->route('agencyId') ?? $agency;
            $agency = Agency::find($agencyId);
        }
        
        if (!$agency) {
            abort(404, 'Agency not found.');
        }

        // Check if user is agency admin
        $isAgencyAdmin = $user->hasRole('agency-admin', $agency) || $user->hasRole('admin', $agency);
        
        if (!$isAgencyAdmin && !$user->isAdmin()) {
            abort(403, 'You do not have permission to access this resource. Agency admin access required.');
        }

        return $next($request);
    }
}

