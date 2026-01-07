<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Set the tenant context for the current request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $organizationId = $request->route('organizationId');

        if ($organizationId && $user->hasAccessToOrganization($organizationId)) {
            $request->attributes->set('organization_id', $organizationId);
            session(['current_organization_id' => $organizationId]);
        } elseif ($user->primaryOrganization()) {
            $request->attributes->set('organization_id', $user->primaryOrganization()->id);
            session(['current_organization_id' => $user->primaryOrganization()->id]);
        }

        return $next($request);
    }
}


