<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlatformSettingRequest;
use App\Http\Requests\Admin\UpdateFeatureFlagRequest;
use App\Models\FeatureFlag;
use App\Services\Admin\PlatformSettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private PlatformSettingsService $settingsService
    ) {
    }

    /**
     * Display platform settings
     */
    public function index()
    {
        $settings = $this->settingsService->getGlobalSettings();
        $featureFlags = $this->settingsService->getFeatureFlags();
        $apiKeys = $this->settingsService->getApiKeys();
        $maintenanceMode = $this->settingsService->isMaintenanceModeEnabled();

        return view('admin.settings.index', compact('settings', 'featureFlags', 'apiKeys', 'maintenanceMode'));
    }

    /**
     * Update platform setting
     */
    public function updateSetting(UpdatePlatformSettingRequest $request)
    {
        $this->settingsService->updateGlobalSetting(
            $request->validated()['key'],
            $request->validated()['value'],
            $request->validated()['type'] ?? 'string'
        );

        return redirect()->route('admin.settings.index')
            ->with('success', 'Platform setting updated.');
    }

    /**
     * Toggle feature flag
     */
    public function toggleFeatureFlag(FeatureFlag $featureFlag)
    {
        $enabled = !$featureFlag->enabled;
        $this->settingsService->toggleFeatureFlag($featureFlag, $enabled);

        return redirect()->route('admin.settings.index')
            ->with('success', "Feature flag {$featureFlag->name} " . ($enabled ? 'enabled' : 'disabled') . '.');
    }

    /**
     * Create or update feature flag
     */
    public function updateFeatureFlag(UpdateFeatureFlagRequest $request, ?FeatureFlag $featureFlag = null)
    {
        $this->settingsService->updateFeatureFlag($request->validated());

        return redirect()->route('admin.settings.index')
            ->with('success', 'Feature flag updated.');
    }

    /**
     * Update API key
     */
    public function updateApiKey(Request $request)
    {
        $request->validate([
            'service' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:500'],
        ]);

        $this->settingsService->updateApiKey(
            $request->validated()['service'],
            $request->validated()['key']
        );

        return redirect()->route('admin.settings.index')
            ->with('success', 'API key updated.');
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenanceMode(Request $request)
    {
        $request->validate([
            'message' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $this->settingsService->enableMaintenanceMode($request->validated()['message'] ?? null);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Maintenance mode enabled.');
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenanceMode()
    {
        $this->settingsService->disableMaintenanceMode();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Maintenance mode disabled.');
    }
}

