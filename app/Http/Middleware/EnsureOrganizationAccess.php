<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->route('organizationId');
        $user = $request->user();

        if (!$user || !$user->hasAccessToOrganization($organizationId)) {
            abort(403, 'You do not have access to this organization.');
        }

        return $next($request);
    }
}


