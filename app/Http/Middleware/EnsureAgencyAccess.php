<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Agency;
use App\Models\ActivityLog;

class EnsureAgencyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $agency = $request->route('agency');
        $user = $request->user();

        // If agency is already resolved via route model binding, use it
        if ($agency instanceof Agency) {
            $agencyId = $agency->id;
        } else {
            // Fallback for agencyId parameter
            $agencyId = $request->route('agencyId') ?? $agency;
        }

        if (!$user || !$user->isAgencyMember($agencyId)) {
            // Log unauthorized access attempt
            ActivityLog::log(
                'unauthorized_access_attempt',
                null,
                $user,
                ['agency_id' => $agencyId, 'route' => $request->path()],
                "Unauthorized attempt to access agency"
            );
            
            abort(403, 'You do not have access to this agency.');
        }

        return $next($request);
    }
}


