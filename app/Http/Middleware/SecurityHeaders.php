<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add security headers to all responses
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.pusher.com wss://ws.pusher.com; frame-ancestors 'none';";
        
        $response->headers->set('Content-Security-Policy', $csp);
        
        // XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Frame Options
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Content Type Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // HSTS (only in production with HTTPS)
        if (app()->environment('production') && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}

