<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\Organization\OrganizationSettingsService;
use App\Http\Requests\Organization\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Organization Settings Controller
 * Handles organization-wide settings management
 * Requires organization admin access
 */
class SettingsController extends Controller
{
    public function __construct(
        private OrganizationSettingsService $settingsService
    ) {}

    /**
     * Display organization settings
     */
    public function index(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $settings = $this->settingsService->getAllSettings($organization);

        return view('organization.settings.index', [
            'organization' => $organization,
            'settings' => $settings,
        ]);
    }

    /**
     * Update general settings
     */
    public function update(UpdateSettingsRequest $request, Organization $organization): JsonResponse
    {
        $organization = $this->settingsService->updateGeneralSettings(
            $organization,
            $request->validated()
        );

        if ($request->has('settings')) {
            foreach ($request->input('settings', []) as $key => $value) {
                $this->settingsService->updateSetting($organization, $key, $value);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'data' => $organization->fresh(),
        ]);
    }

    /**
     * Get a specific setting
     */
    public function getSetting(Request $request, Organization $organization, string $key): JsonResponse
    {
        $this->authorize('view', $organization);

        $value = $this->settingsService->getSetting($organization, $key);

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }

    /**
     * Update a specific setting
     */
    public function updateSetting(Request $request, Organization $organization, string $key): JsonResponse
    {
        $this->authorize('update', $organization);

        $request->validate([
            'value' => ['required'],
        ]);

        $this->settingsService->updateSetting($organization, $key, $request->input('value'));

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully.',
        ]);
    }

    /**
     * Delete a specific setting
     */
    public function deleteSetting(Request $request, Organization $organization, string $key): JsonResponse
    {
        $this->authorize('update', $organization);

        $this->settingsService->deleteSetting($organization, $key);

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully.',
        ]);
    }
}

