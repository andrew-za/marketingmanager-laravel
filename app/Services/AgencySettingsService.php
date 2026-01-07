<?php

namespace App\Services;

use App\Models\Agency;
use Illuminate\Support\Facades\DB;

/**
 * Agency Settings Service
 * Handles agency-wide settings management
 */
class AgencySettingsService
{
    /**
     * Update agency profile settings
     */
    public function updateProfile(Agency $agency, array $data): Agency
    {
        return DB::transaction(function () use ($agency, $data) {
            $agency->update([
                'name' => $data['name'] ?? $agency->name,
                'logo' => $data['logo'] ?? $agency->logo,
                'status' => $data['status'] ?? $agency->status,
            ]);

            return $agency->fresh();
        });
    }

    /**
     * Update agency branding configuration
     */
    public function updateBranding(Agency $agency, array $data): Agency
    {
        $settings = $agency->settings ?? [];
        $settings['branding'] = array_merge($settings['branding'] ?? [], [
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'logo' => $data['logo'] ?? null,
            'favicon' => $data['favicon'] ?? null,
        ]);

        return DB::transaction(function () use ($agency, $settings) {
            $agency->update(['settings' => $settings]);
            return $agency->fresh();
        });
    }

    /**
     * Update default settings
     */
    public function updateDefaults(Agency $agency, array $data): Agency
    {
        $settings = $agency->settings ?? [];
        $settings['defaults'] = array_merge($settings['defaults'] ?? [], [
            'timezone' => $data['timezone'] ?? null,
            'locale' => $data['locale'] ?? null,
            'currency' => $data['currency'] ?? null,
            'date_format' => $data['date_format'] ?? null,
        ]);

        return DB::transaction(function () use ($agency, $settings) {
            $agency->update(['settings' => $settings]);
            return $agency->fresh();
        });
    }

    /**
     * Update integration settings
     */
    public function updateIntegrations(Agency $agency, array $data): Agency
    {
        $settings = $agency->settings ?? [];
        $settings['integrations'] = array_merge($settings['integrations'] ?? [], $data);

        return DB::transaction(function () use ($agency, $settings) {
            $agency->update(['settings' => $settings]);
            return $agency->fresh();
        });
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Agency $agency, array $preferences): Agency
    {
        $settings = $agency->settings ?? [];
        $settings['notifications'] = array_merge($settings['notifications'] ?? [], $preferences);

        return DB::transaction(function () use ($agency, $settings) {
            $agency->update(['settings' => $settings]);
            return $agency->fresh();
        });
    }

    /**
     * Get agency settings
     */
    public function getSettings(Agency $agency): array
    {
        return $agency->settings ?? [
            'branding' => [],
            'defaults' => [],
            'integrations' => [],
            'notifications' => [],
        ];
    }
}

