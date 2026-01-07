<?php

namespace Tests\Integration;

use App\Models\Organization;
use App\Models\SocialConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->user->organizations()->attach($this->organization->id, ['role_id' => 1]);
        
        $this->actingAs($this->user);
    }

    public function testUserCanInitiateOAuthFlow(): void
    {
        $response = $this->get("/main/{$this->organization->id}/social-connections/facebook/redirect");

        $response->assertRedirect();
        $this->assertStringContainsString('facebook.com', $response->headers->get('Location'));
    }

    public function testUserCanCompleteOAuthCallback(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'access_token' => 'test_token',
                'token_type' => 'bearer',
                'expires_in' => 3600,
            ]),
        ]);

        $response = $this->get("/main/{$this->organization->id}/social-connections/facebook/callback", [
            'code' => 'test_code',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('social_connections', [
            'organization_id' => $this->organization->id,
            'platform' => 'facebook',
            'status' => 'connected',
        ]);
    }

    public function testOAuthCallbackHandlesErrors(): void
    {
        $response = $this->get("/main/{$this->organization->id}/social-connections/facebook/callback", [
            'error' => 'access_denied',
            'error_description' => 'User denied access',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    public function testUserCanRefreshToken(): void
    {
        $connection = SocialConnection::factory()->create([
            'organization_id' => $this->organization->id,
            'platform' => 'facebook',
            'status' => 'connected',
            'token_expires_at' => now()->subHour(),
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'access_token' => 'new_token',
                'expires_in' => 3600,
            ]),
        ]);

        $response = $this->post("/main/{$this->organization->id}/social-connections/{$connection->id}/refresh");

        $response->assertStatus(200);
        $connection->refresh();
        $this->assertNotEquals('new_token', $connection->access_token);
    }
}

