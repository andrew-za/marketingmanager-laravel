<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Brand;
use App\Models\Organization;

/**
 * Ensure brand context is valid for brand-scoped routes
 * Validates brandId query parameter and ensures brand belongs to organization
 */
class EnsureBrandContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->route('organizationId');
        $brandId = $request->query('brandId');
        $user = $request->user();

        if (!$user) {
            abort(403, 'You must be authenticated to access this resource.');
        }

        if (!$brandId) {
            abort(400, 'Brand ID is required for this resource.');
        }

        if (!$organizationId) {
            abort(400, 'Organization ID is required.');
        }

        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            abort(404, 'Organization not found.');
        }

        $brand = Brand::where('organization_id', $organizationId)
            ->where('id', $brandId)
            ->first();

        if (!$brand) {
            abort(404, 'Brand not found or does not belong to this organization.');
        }

        // Check if user has permission to view this brand
        if (!$user->can('view', $brand)) {
            abort(403, 'You do not have permission to access this brand.');
        }

        // Add brand to request for use in controllers
        $request->merge(['brand' => $brand]);

        return $next($request);
    }
}

