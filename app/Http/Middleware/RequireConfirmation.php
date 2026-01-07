<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Require confirmation for sensitive operations
 * Checks for confirmation token or flag in request
 */
class RequireConfirmation
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if confirmation is required
        $requiresConfirmation = $request->input('_confirm', false);
        $confirmationToken = $request->input('_confirmation_token');

        // If confirmation token is provided and matches session, allow
        if ($confirmationToken && $request->session()->get('action_confirmation_token') === $confirmationToken) {
            // Clear the token after use
            $request->session()->forget('action_confirmation_token');
            return $next($request);
        }

        // If confirmation is required but not provided, return error
        if ($requiresConfirmation && !$confirmationToken) {
            abort(422, 'This action requires confirmation. Please confirm your intent.');
        }

        return $next($request);
    }
}

