<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiting middleware for API requests
 * Limits to 60 requests per minute per user
 */
class ApiRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $key = $user ? 'api:user:'.$user->id : 'api:ip:'.$request->ip();

        $executed = RateLimiter::attempt(
            $key,
            $perMinute = 60,
            function () use ($next, $request) {
                return $next($request);
            }
        );

        if (!$executed) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Too many requests. Please try again later.',
                    'details' => [
                        'limit' => 60,
                        'period' => '1 minute',
                    ],
                ],
            ], 429);
        }

        return $executed;
    }
}

