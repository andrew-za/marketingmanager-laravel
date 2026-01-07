<?php

namespace App\Services\SocialMedia;

use App\Models\SocialConnection;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class TokenRefreshService
{
    public function refreshToken(SocialConnection $connection): bool
    {
        try {
            if (!$connection->refresh_token) {
                Log::warning("No refresh token available for connection {$connection->id}");
                return false;
            }

            $refreshedToken = $this->refreshTokenForPlatform(
                $connection->platform,
                $connection->refresh_token
            );

            if (!$refreshedToken) {
                return false;
            }

            $connection->update([
                'access_token' => $refreshedToken['access_token'],
                'refresh_token' => $refreshedToken['refresh_token'] ?? $connection->refresh_token,
                'token_expires_at' => $refreshedToken['expires_at'] ?? now()->addHours(2),
                'token_metadata' => $refreshedToken['metadata'] ?? [],
                'status' => 'connected',
                'error_message' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to refresh token for connection {$connection->id}: " . $e->getMessage());
            $connection->markAsError($e->getMessage());
            return false;
        }
    }

    protected function refreshTokenForPlatform(string $platform, string $refreshToken): ?array
    {
        return match ($platform) {
            'facebook' => $this->refreshFacebookToken($refreshToken),
            'instagram' => $this->refreshInstagramToken($refreshToken),
            'linkedin' => $this->refreshLinkedInToken($refreshToken),
            'twitter' => $this->refreshTwitterToken($refreshToken),
            'tiktok' => $this->refreshTikTokToken($refreshToken),
            'pinterest' => $this->refreshPinterestToken($refreshToken),
            default => null,
        };
    }

    protected function refreshFacebookToken(string $refreshToken): ?array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://graph.facebook.com/v18.0/oauth/access_token', [
            'form_params' => [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'fb_exchange_token' => $refreshToken,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['access_token'] ?? $refreshToken,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000),
            'metadata' => $data,
        ];
    }

    protected function refreshInstagramToken(string $refreshToken): ?array
    {
        return $this->refreshFacebookToken($refreshToken);
    }

    protected function refreshLinkedInToken(string $refreshToken): ?array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => config('services.linkedin.client_id'),
                'client_secret' => config('services.linkedin.client_secret'),
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000),
            'metadata' => $data,
        ];
    }

    protected function refreshTwitterToken(string $refreshToken): ?array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.twitter.com/2/oauth2/token', [
            'auth' => [
                config('services.twitter.client_id'),
                config('services.twitter.client_secret'),
            ],
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 7200),
            'metadata' => $data,
        ];
    }

    protected function refreshTikTokToken(string $refreshToken): ?array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_key' => config('services.tiktok.client_id'),
                'client_secret' => config('services.tiktok.client_secret'),
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 7200),
            'metadata' => $data,
        ];
    }

    protected function refreshPinterestToken(string $refreshToken): ?array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.pinterest.com/v5/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => config('services.pinterest.client_id'),
                'client_secret' => config('services.pinterest.client_secret'),
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return [
            'access_token' => $data['access_token'] ?? null,
            'refresh_token' => $data['refresh_token'] ?? $refreshToken,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 2592000),
            'metadata' => $data,
        ];
    }

    public function refreshExpiredTokens(): int
    {
        $expiredConnections = SocialConnection::where('status', 'connected')
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<', now()->subMinutes(5))
            ->get();

        $refreshed = 0;
        foreach ($expiredConnections as $connection) {
            if ($this->refreshToken($connection)) {
                $refreshed++;
            }
        }

        return $refreshed;
    }
}


