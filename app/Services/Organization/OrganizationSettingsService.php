<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\DB;

class OrganizationSettingsService
{
    public function updateGeneralSettings(Organization $organization, array $data): Organization
    {
        return DB::transaction(function () use ($organization, $data) {
            $organization->update([
                'name' => $data['name'] ?? $organization->name,
                'timezone' => $data['timezone'] ?? $organization->timezone,
                'locale' => $data['locale'] ?? $organization->locale,
                'country_code' => $data['country_code'] ?? $organization->country_code,
            ]);

            return $organization->fresh();
        });
    }

    public function updateSetting(Organization $organization, string $key, mixed $value): OrganizationSetting
    {
        return DB::transaction(function () use ($organization, $key, $value) {
            return OrganizationSetting::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'key' => $key,
                ],
                [
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]
            );
        });
    }

    public function getSetting(Organization $organization, string $key, mixed $default = null): mixed
    {
        $setting = OrganizationSetting::where('organization_id', $organization->id)
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        $value = json_decode($setting->value, true);
        return json_last_error() === JSON_ERROR_NONE ? $value : $setting->value;
    }

    public function getAllSettings(Organization $organization): array
    {
        return OrganizationSetting::where('organization_id', $organization->id)
            ->get()
            ->mapWithKeys(function ($setting) {
                $value = json_decode($setting->value, true);
                return [$setting->key => json_last_error() === JSON_ERROR_NONE ? $value : $setting->value];
            })
            ->toArray();
    }

    public function deleteSetting(Organization $organization, string $key): bool
    {
        return OrganizationSetting::where('organization_id', $organization->id)
            ->where('key', $key)
            ->delete();
    }
}

