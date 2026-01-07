<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class StorageSourceService
{
    public function getStorageSources(Organization $organization): array
    {
        $sources = OrganizationSetting::where('organization_id', $organization->id)
            ->where('key', 'like', 'storage_source_%')
            ->get()
            ->map(function ($setting) {
                $data = json_decode($setting->value, true);
                $provider = str_replace('storage_source_', '', $setting->key);
                
                return [
                    'provider' => $provider,
                    'name' => $data['name'] ?? $provider,
                    'is_connected' => !empty($data['access_token']),
                    'connected_at' => $data['connected_at'] ?? null,
                ];
            })
            ->toArray();

        return $sources;
    }

    public function connectStorageSource(Organization $organization, string $provider, array $credentials): void
    {
        DB::transaction(function () use ($organization, $provider, $credentials) {
            $data = [
                'name' => $credentials['name'] ?? $provider,
                'access_token' => Crypt::encryptString($credentials['access_token'] ?? ''),
                'refresh_token' => isset($credentials['refresh_token']) 
                    ? Crypt::encryptString($credentials['refresh_token']) 
                    : null,
                'connected_at' => now()->toDateTimeString(),
                'settings' => $credentials['settings'] ?? [],
            ];

            OrganizationSetting::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'key' => "storage_source_{$provider}",
                ],
                [
                    'value' => json_encode($data),
                ]
            );
        });
    }

    public function disconnectStorageSource(Organization $organization, string $provider): void
    {
        DB::transaction(function () use ($organization, $provider) {
            OrganizationSetting::where('organization_id', $organization->id)
                ->where('key', "storage_source_{$provider}")
                ->delete();
        });
    }

    public function getStorageSourceCredentials(Organization $organization, string $provider): ?array
    {
        $setting = OrganizationSetting::where('organization_id', $organization->id)
            ->where('key', "storage_source_{$provider}")
            ->first();

        if (!$setting) {
            return null;
        }

        $data = json_decode($setting->value, true);
        
        if (isset($data['access_token'])) {
            $data['access_token'] = Crypt::decryptString($data['access_token']);
        }
        
        if (isset($data['refresh_token'])) {
            $data['refresh_token'] = Crypt::decryptString($data['refresh_token']);
        }

        return $data;
    }

    public function updateStorageSourceSettings(Organization $organization, string $provider, array $settings): void
    {
        DB::transaction(function () use ($organization, $provider, $settings) {
            $setting = OrganizationSetting::where('organization_id', $organization->id)
                ->where('key', "storage_source_{$provider}")
                ->first();

            if (!$setting) {
                return;
            }

            $data = json_decode($setting->value, true);
            $data['settings'] = array_merge($data['settings'] ?? [], $settings);

            $setting->update(['value' => json_encode($data)]);
        });
    }
}

