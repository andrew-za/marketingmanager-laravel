<?php

namespace App\Http\Controllers\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\SocialConnection;
use App\Models\Channel;
use App\Services\SocialMedia\TokenRefreshService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    protected TokenRefreshService $tokenRefreshService;

    public function __construct(TokenRefreshService $tokenRefreshService)
    {
        $this->tokenRefreshService = $tokenRefreshService;
    }

    public function redirectToProvider(Request $request, string $platform)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'channel_id' => 'nullable|exists:channels,id',
        ]);

        $request->session()->put('social_auth', [
            'organization_id' => $request->organization_id,
            'channel_id' => $request->channel_id,
            'platform' => $platform,
        ]);

        $scopes = $this->getScopesForPlatform($platform);

        return Socialite::driver($platform)
            ->scopes($scopes)
            ->redirect();
    }

    public function handleProviderCallback(Request $request, string $platform)
    {
        try {
            $sessionData = $request->session()->get('social_auth');

            if (!$sessionData || $sessionData['platform'] !== $platform) {
                return redirect()->route('main.dashboard', ['organizationId' => $sessionData['organization_id'] ?? 1])
                    ->with('error', 'Invalid session data');
            }

            $user = Socialite::driver($platform)->user();
            $organizationId = $sessionData['organization_id'];
            $channelId = $sessionData['channel_id'] ?? null;

            $connection = $this->createOrUpdateConnection(
                $platform,
                $user,
                $organizationId,
                $channelId
            );

            $request->session()->forget('social_auth');

            return redirect()->route('main.dashboard', ['organizationId' => $organizationId])
                ->with('success', "Successfully connected {$platform} account");
        } catch (\Exception $e) {
            Log::error("OAuth callback error for {$platform}: " . $e->getMessage());
            return redirect()->route('main.dashboard', ['organizationId' => $sessionData['organization_id'] ?? 1])
                ->with('error', 'Failed to connect account: ' . $e->getMessage());
        }
    }

    protected function createOrUpdateConnection(
        string $platform,
        $socialUser,
        int $organizationId,
        ?int $channelId
    ): SocialConnection {
        $existingConnection = SocialConnection::where('organization_id', $organizationId)
            ->where('platform', $platform)
            ->where('account_id', $socialUser->id)
            ->first();

        $tokenData = $this->extractTokenData($socialUser, $platform);

        if ($existingConnection) {
            $existingConnection->update([
                'account_name' => $socialUser->name ?? $socialUser->nickname ?? 'Unknown',
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? $existingConnection->refresh_token,
                'token_expires_at' => $tokenData['expires_at'] ?? now()->addHours(2),
                'token_metadata' => $tokenData['metadata'] ?? [],
                'status' => 'connected',
                'error_message' => null,
                'last_sync_at' => now(),
            ]);

            return $existingConnection;
        }

        return SocialConnection::create([
            'organization_id' => $organizationId,
            'channel_id' => $channelId,
            'platform' => $platform,
            'account_id' => $socialUser->id,
            'account_name' => $socialUser->name ?? $socialUser->nickname ?? 'Unknown',
            'account_type' => $socialUser->user['account_type'] ?? null,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'token_expires_at' => $tokenData['expires_at'] ?? now()->addHours(2),
            'token_metadata' => $tokenData['metadata'] ?? [],
            'status' => 'connected',
            'last_sync_at' => now(),
        ]);
    }

    protected function extractTokenData($socialUser, string $platform): array
    {
        $token = $socialUser->token;
        $refreshToken = $socialUser->refreshToken ?? null;
        $expiresIn = $socialUser->expiresIn ?? 7200;

        return [
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'expires_at' => now()->addSeconds($expiresIn),
            'metadata' => [
                'id' => $socialUser->id,
                'name' => $socialUser->name ?? null,
                'email' => $socialUser->email ?? null,
                'avatar' => $socialUser->avatar ?? null,
            ],
        ];
    }

    protected function getScopesForPlatform(string $platform): array
    {
        return match ($platform) {
            'facebook' => ['pages_manage_posts', 'pages_read_engagement', 'pages_show_list'],
            'instagram' => ['instagram_basic', 'instagram_content_publish', 'pages_show_list'],
            'linkedin' => ['w_member_social', 'w_organization_social', 'r_liteprofile', 'r_emailaddress'],
            'twitter' => ['tweet.read', 'tweet.write', 'users.read', 'offline.access'],
            'tiktok' => ['user.info.basic', 'video.upload', 'video.publish'],
            'pinterest' => ['boards:read', 'pins:read', 'pins:write'],
            default => [],
        };
    }

    public function disconnect(Request $request, SocialConnection $connection)
    {
        $this->authorize('update', $connection);

        $connection->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
        ]);

        return response()->json(['message' => 'Connection disconnected successfully']);
    }

    public function refresh(SocialConnection $connection)
    {
        $this->authorize('update', $connection);

        if ($this->tokenRefreshService->refreshToken($connection)) {
            return response()->json(['message' => 'Token refreshed successfully']);
        }

        return response()->json(['message' => 'Failed to refresh token'], 400);
    }
}


