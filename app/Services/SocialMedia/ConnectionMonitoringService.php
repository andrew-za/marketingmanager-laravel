<?php

namespace App\Services\SocialMedia;

use App\Models\SocialConnection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ConnectionMonitoringService
{
    public function checkConnectionStatus(SocialConnection $connection): bool
    {
        try {
            $isValid = $this->validateConnection($connection);

            if ($isValid) {
                $connection->markAsConnected();
                Cache::put("connection_status_{$connection->id}", 'connected', 300);
                return true;
            } else {
                $connection->markAsError('Connection validation failed');
                Cache::put("connection_status_{$connection->id}", 'error', 300);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Connection check failed for {$connection->id}: " . $e->getMessage());
            $connection->markAsError($e->getMessage());
            Cache::put("connection_status_{$connection->id}", 'error', 300);
            return false;
        }
    }

    protected function validateConnection(SocialConnection $connection): bool
    {
        if ($connection->isExpired()) {
            $tokenRefreshService = app(TokenRefreshService::class);
            return $tokenRefreshService->refreshToken($connection);
        }

        return match ($connection->platform) {
            'facebook' => $this->validateFacebookConnection($connection),
            'instagram' => $this->validateInstagramConnection($connection),
            'linkedin' => $this->validateLinkedInConnection($connection),
            'twitter' => $this->validateTwitterConnection($connection),
            'tiktok' => $this->validateTikTokConnection($connection),
            'pinterest' => $this->validatePinterestConnection($connection),
            default => false,
        };
    }

    protected function validateFacebookConnection(SocialConnection $connection): bool
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get("https://graph.facebook.com/v18.0/me", [
                'query' => [
                    'access_token' => $connection->access_token,
                    'fields' => 'id,name',
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validateInstagramConnection(SocialConnection $connection): bool
    {
        return $this->validateFacebookConnection($connection);
    }

    protected function validateLinkedInConnection(SocialConnection $connection): bool
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get('https://api.linkedin.com/v2/userinfo', [
                'headers' => [
                    'Authorization' => "Bearer {$connection->access_token}",
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validateTwitterConnection(SocialConnection $connection): bool
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get('https://api.twitter.com/2/users/me', [
                'headers' => [
                    'Authorization' => "Bearer {$connection->access_token}",
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validateTikTokConnection(SocialConnection $connection): bool
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get('https://open.tiktokapis.com/v2/user/info/', [
                'headers' => [
                    'Authorization' => "Bearer {$connection->access_token}",
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validatePinterestConnection(SocialConnection $connection): bool
    {
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get('https://api.pinterest.com/v5/user_account', [
                'headers' => [
                    'Authorization' => "Bearer {$connection->access_token}",
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function monitorAllConnections(): array
    {
        $connections = SocialConnection::where('status', 'connected')->get();
        $results = [
            'checked' => 0,
            'valid' => 0,
            'invalid' => 0,
        ];

        foreach ($connections as $connection) {
            $results['checked']++;
            if ($this->checkConnectionStatus($connection)) {
                $results['valid']++;
            } else {
                $results['invalid']++;
            }
        }

        return $results;
    }
}


