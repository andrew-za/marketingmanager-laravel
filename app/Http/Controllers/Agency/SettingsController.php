<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Services\AgencySettingsService;
use App\Http\Requests\Agency\UpdateAgencyProfileRequest;
use App\Http\Requests\Agency\UpdateAgencyBrandingRequest;
use App\Http\Requests\Agency\UpdateAgencyDefaultsRequest;
use App\Http\Requests\Agency\UpdateAgencyIntegrationsRequest;
use App\Http\Requests\Agency\UpdateAgencyNotificationsRequest;
use Illuminate\Http\Request;

/**
 * Agency Settings Controller
 * Handles agency-wide settings configuration
 * Requires agency admin access
 */
class SettingsController extends Controller
{
    public function __construct(
        private AgencySettingsService $settingsService
    ) {}

    /**
     * Display agency settings
     */
    public function index(Request $request, Agency $agency)
    {
        $settings = $this->settingsService->getSettings($agency);

        return view('agency.settings.index', [
            'agency' => $agency,
            'settings' => $settings,
        ]);
    }

    /**
     * Update agency profile
     */
    public function updateProfile(UpdateAgencyProfileRequest $request, Agency $agency)
    {
        $agency = $this->settingsService->updateProfile($agency, $request->validated());

        return redirect()->route('agency.settings', ['agency' => $agency])
            ->with('success', 'Agency profile updated successfully.');
    }

    /**
     * Update agency branding
     */
    public function updateBranding(UpdateAgencyBrandingRequest $request, Agency $agency)
    {
        $agency = $this->settingsService->updateBranding($agency, $request->validated());

        return redirect()->route('agency.settings', ['agency' => $agency])
            ->with('success', 'Agency branding updated successfully.');
    }

    /**
     * Update default settings
     */
    public function updateDefaults(UpdateAgencyDefaultsRequest $request, Agency $agency)
    {
        $agency = $this->settingsService->updateDefaults($agency, $request->validated());

        return redirect()->route('agency.settings', ['agency' => $agency])
            ->with('success', 'Default settings updated successfully.');
    }

    /**
     * Update integration settings
     */
    public function updateIntegrations(UpdateAgencyIntegrationsRequest $request, Agency $agency)
    {
        $agency = $this->settingsService->updateIntegrations($agency, $request->validated());

        return redirect()->route('agency.settings', ['agency' => $agency])
            ->with('success', 'Integration settings updated successfully.');
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(UpdateAgencyNotificationsRequest $request, Agency $agency)
    {
        $agency = $this->settingsService->updateNotificationPreferences($agency, $request->validated());

        return redirect()->route('agency.settings', ['agency' => $agency])
            ->with('success', 'Notification preferences updated successfully.');
    }
}

