<?php

namespace App\Services\Admin;

use App\Models\PlatformSetting;
use App\Models\FeatureFlag;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class PlatformSettingsService
{
    /**
     * Get global platform settings
     */
    public function getGlobalSettings(): array
    {
        return PlatformSetting::where('is_global', true)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Update global platform setting
     */
    public function updateGlobalSetting(string $key, mixed $value, ?string $type = 'string'): PlatformSetting
    {
        $setting = PlatformSetting::firstOrCreate(
            ['key' => $key, 'is_global' => true],
            ['platform' => 'global', 'type' => $type]
        );

        $setting->update(['value' => $value]);
        Cache::forget("platform_setting_{$key}");

        return $setting;
    }

    /**
     * Get feature flags
     */
    public function getFeatureFlags(): \Illuminate\Database\Eloquent\Collection
    {
        return FeatureFlag::orderBy('name')->get();
    }

    /**
     * Toggle feature flag
     */
    public function toggleFeatureFlag(FeatureFlag $featureFlag, bool $enabled): FeatureFlag
    {
        $featureFlag->update(['enabled' => $enabled]);
        Cache::forget("feature_flag_{$featureFlag->name}");

        return $featureFlag->fresh();
    }

    /**
     * Create or update feature flag
     */
    public function updateFeatureFlag(array $data): FeatureFlag
    {
        $featureFlag = FeatureFlag::updateOrCreate(
            ['name' => $data['name']],
            $data
        );

        Cache::forget("feature_flag_{$featureFlag->name}");

        return $featureFlag;
    }

    /**
     * Get API keys (stored in platform settings)
     */
    public function getApiKeys(): array
    {
        return PlatformSetting::where('is_global', true)
            ->where('key', 'like', 'api_key_%')
            ->get()
            ->mapWithKeys(function ($setting) {
                return [str_replace('api_key_', '', $setting->key) => $setting->value];
            })
            ->toArray();
    }

    /**
     * Update API key
     */
    public function updateApiKey(string $service, string $key): PlatformSetting
    {
        return $this->updateGlobalSetting("api_key_{$service}", $key, 'string');
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenanceMode(?string $message = null): void
    {
        Artisan::call('down', [
            '--message' => $message ?? 'System is under maintenance',
        ]);
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenanceMode(): void
    {
        Artisan::call('up');
    }

    /**
     * Check if maintenance mode is enabled
     */
    public function isMaintenanceModeEnabled(): bool
    {
        return file_exists(storage_path('framework/down'));
    }

    /**
     * Get system logs
     */
    public function getSystemLogs(array $filters = [], int $perPage = 50)
    {
        $query = SystemLog::query();

        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Log system event
     */
    public function logSystemEvent(string $level, string $message, array $context = [], ?int $userId = null): SystemLog
    {
        return SystemLog::create([
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'total_users' => \App\Models\User::count(),
            'total_organizations' => \App\Models\Organization::count(),
            'total_campaigns' => \App\Models\Campaign::count(),
            'total_posts' => \App\Models\ScheduledPost::count(),
            'pending_moderation' => \App\Models\ModerationQueue::where('status', 'pending')->count(),
            'system_errors_today' => SystemLog::where('level', 'error')
                ->whereDate('created_at', today())
                ->count(),
        ];
    }
}

