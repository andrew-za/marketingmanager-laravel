<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Organization API Controller
 */
class OrganizationController extends Controller
{
    /**
     * List user's organizations
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $organizations = $user->organizations()->paginate();

        return OrganizationResource::collection($organizations);
    }

    /**
     * Get organization details
     */
    public function show(Request $request, Organization $organization)
    {
        $this->authorize('view', $organization);

        return response()->json([
            'success' => true,
            'data' => new OrganizationResource($organization->load(['brands', 'subscription'])),
            'message' => 'Organization retrieved successfully',
        ]);
    }

    /**
     * Create new organization
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string'],
            'locale' => ['required', 'string'],
            'country_code' => ['required', 'string', 'size:2'],
        ]);

        $organization = Organization::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'timezone' => $request->timezone,
            'locale' => $request->locale,
            'country_code' => $request->country_code,
            'status' => 'active',
        ]);

        $request->user()->organizations()->attach($organization->id, ['role_id' => 1]);

        return response()->json([
            'success' => true,
            'data' => new OrganizationResource($organization),
            'message' => 'Organization created successfully',
        ], 201);
    }

    /**
     * Update organization
     */
    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'timezone' => ['sometimes', 'string'],
            'locale' => ['sometimes', 'string'],
            'country_code' => ['sometimes', 'string', 'size:2'],
        ]);

        $organization->update($request->only(['name', 'timezone', 'locale', 'country_code']));

        return response()->json([
            'success' => true,
            'data' => new OrganizationResource($organization),
            'message' => 'Organization updated successfully',
        ]);
    }

    /**
     * Delete organization
     */
    public function destroy(Request $request, Organization $organization)
    {
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->json([
            'success' => true,
            'message' => 'Organization deleted successfully',
        ]);
    }
}

