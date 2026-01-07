<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\Organization\StorageSourceService;
use App\Http\Requests\Organization\ConnectStorageSourceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Organization Storage Source Controller
 * Handles cloud storage integration management
 * Requires organization admin access
 */
class StorageSourceController extends Controller
{
    public function __construct(
        private StorageSourceService $storageService
    ) {}

    /**
     * Display storage sources
     */
    public function index(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $sources = $this->storageService->getStorageSources($organization);

        return view('organization.storage-sources.index', [
            'organization' => $organization,
            'sources' => $sources,
        ]);
    }

    /**
     * Connect storage source
     */
    public function connect(ConnectStorageSourceRequest $request, Organization $organization): JsonResponse
    {
        $this->storageService->connectStorageSource(
            $organization,
            $request->input('provider'),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Storage source connected successfully.',
        ], 201);
    }

    /**
     * Disconnect storage source
     */
    public function disconnect(Request $request, Organization $organization, string $provider): JsonResponse
    {
        $this->authorize('update', $organization);

        $request->validate([
            'provider' => ['required', 'string', 'in:s3,google_drive,dropbox'],
        ]);

        $this->storageService->disconnectStorageSource($organization, $provider);

        return response()->json([
            'success' => true,
            'message' => 'Storage source disconnected successfully.',
        ]);
    }

    /**
     * Get storage source details
     */
    public function show(Request $request, Organization $organization, string $provider): JsonResponse
    {
        $this->authorize('view', $organization);

        $credentials = $this->storageService->getStorageSourceCredentials($organization, $provider);

        if (!$credentials) {
            return response()->json([
                'success' => false,
                'message' => 'Storage source not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'name' => $credentials['name'] ?? $provider,
                'is_connected' => !empty($credentials['access_token']),
            ],
        ]);
    }

    /**
     * Update storage source settings
     */
    public function updateSettings(Request $request, Organization $organization, string $provider): JsonResponse
    {
        $this->authorize('update', $organization);

        $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $this->storageService->updateStorageSourceSettings(
            $organization,
            $provider,
            $request->input('settings')
        );

        return response()->json([
            'success' => true,
            'message' => 'Storage source settings updated successfully.',
        ]);
    }
}

