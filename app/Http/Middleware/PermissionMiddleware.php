<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if (!$user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have the required permission to access this resource.');
        }

        return $next($request);
    }
}


