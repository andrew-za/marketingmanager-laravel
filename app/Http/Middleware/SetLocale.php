<?php

namespace App\Http\Middleware;

use App\Services\Localization\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to detect and set the application locale based on user preferences
 */
class SetLocale
{
    protected LocaleService $localeService;

    public function __construct(LocaleService $localeService)
    {
        $this->localeService = $localeService;
    }

    /**
     * Handle an incoming request and set the appropriate locale
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $organization = $this->getOrganizationFromRequest($request);

        $this->localeService->detectAndSetLocale($user, $organization);

        return $next($request);
    }

    /**
     * Get the organization from the current request context
     */
    protected function getOrganizationFromRequest(Request $request): mixed
    {
        // Try to get organization from route parameter
        $organizationId = $request->route('organization');
        
        if ($organizationId) {
            return \App\Models\Organization::find($organizationId);
        }

        // Try to get from authenticated user's primary organization
        if ($user = $request->user()) {
            return $user->primaryOrganization();
        }

        return null;
    }
}


