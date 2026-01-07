<?php

namespace Tests\Integration;

use App\Models\Campaign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
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
        
        Sanctum::actingAs($this->user);
    }

    public function testApiCanListCampaigns(): void
    {
        Campaign::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/v1/campaigns');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'status', 'created_at'],
            ],
        ]);
    }

    public function testApiCanCreateCampaign(): void
    {
        $response = $this->postJson('/api/v1/campaigns', [
            'name' => 'API Campaign',
            'description' => 'Created via API',
            'status' => 'draft',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'name', 'status'],
        ]);
        
        $this->assertDatabaseHas('campaigns', [
            'name' => 'API Campaign',
        ]);
    }

    public function testApiCanUpdateCampaign(): void
    {
        $campaign = Campaign::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->putJson("/api/v1/campaigns/{$campaign->id}", [
            'name' => 'Updated via API',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated via API',
        ]);
    }

    public function testApiCanDeleteCampaign(): void
    {
        $campaign = Campaign::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->deleteJson("/api/v1/campaigns/{$campaign->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function testApiRequiresAuthentication(): void
    {
        Sanctum::actingAs(null);

        $response = $this->getJson('/api/v1/campaigns');

        $response->assertStatus(401);
    }

    public function testApiRateLimiting(): void
    {
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/v1/campaigns');
        }

        $response->assertStatus(429);
    }
}

