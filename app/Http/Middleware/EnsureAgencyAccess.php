<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgencyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $agencyId = $request->route('agencyId');
        $user = $request->user();

        if (!$user || !$user->isAgencyMember($agencyId)) {
            abort(403, 'You do not have access to this agency.');
        }

        return $next($request);
    }
}

