<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class SessionService
{
    /**
     * Create or update user session record
     */
    public function createOrUpdateSession(User $user, Request $request): UserSession
    {
        $sessionId = $request->session()->getId();
        $userAgent = $request->userAgent();

        return UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'session_id' => $sessionId,
            ],
            [
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'device_type' => $this->getDeviceType($userAgent),
                'device_name' => $this->extractDeviceName($userAgent),
                'browser' => $this->extractBrowser($userAgent),
                'platform' => $this->extractPlatform($userAgent),
                'last_activity' => now(),
                'is_active' => true,
            ]
        );
    }

    /**
     * Get all active sessions for a user
     */
    public function getActiveSessions(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return UserSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(User $user, string $sessionId): bool
    {
        $session = UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$session) {
            return false;
        }

        $session->update(['is_active' => false]);

        if ($sessionId === session()->getId()) {
            session()->invalidate();
        }

        return true;
    }

    /**
     * Revoke all other sessions except current
     */
    public function revokeOtherSessions(User $user, string $currentSessionId): int
    {
        return UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->update(['is_active' => false]);
    }

    /**
     * Clean up inactive sessions older than specified days
     */
    public function cleanupInactiveSessions(int $days = 30): int
    {
        return UserSession::where('is_active', false)
            ->where('last_activity', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Determine device type from user agent string
     */
    protected function getDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad/', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Extract device name from user agent
     */
    protected function extractDeviceName(string $userAgent): ?string
    {
        if (preg_match('/(iPhone|iPad|iPod|Android|Windows Phone)/i', $userAgent, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract browser from user agent
     */
    protected function extractBrowser(string $userAgent): ?string
    {
        if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera|MSIE|Trident)/i', $userAgent, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract platform from user agent
     */
    protected function extractPlatform(string $userAgent): ?string
    {
        if (preg_match('/(Windows|Mac|Linux|Android|iOS|iPhone|iPad)/i', $userAgent, $matches)) {
            return $matches[1];
        }

        return null;
    }
}

