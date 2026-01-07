<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignCrudTest extends TestCase
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

    public function testUserCanCreateCampaign(): void
    {
        $response = $this->post("/main/{$this->organization->id}/campaigns", [
            'name' => 'New Campaign',
            'description' => 'Campaign Description',
            'status' => 'draft',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => 'New Campaign',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function testUserCanViewCampaigns(): void
    {
        Campaign::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get("/main/{$this->organization->id}/campaigns");

        $response->assertStatus(200);
        $response->assertViewIs('campaigns.index');
    }

    public function testUserCanViewSingleCampaign(): void
    {
        $campaign = Campaign::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get("/main/{$this->organization->id}/campaigns/{$campaign->id}");

        $response->assertStatus(200);
        $response->assertViewHas('campaign');
    }

    public function testUserCanUpdateCampaign(): void
    {
        $campaign = Campaign::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->put("/main/{$this->organization->id}/campaigns/{$campaign->id}", [
            'name' => 'Updated Campaign Name',
            'description' => $campaign->description,
            'status' => $campaign->status,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated Campaign Name',
        ]);
    }

    public function testUserCanDeleteCampaign(): void
    {
        $campaign = Campaign::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->delete("/main/{$this->organization->id}/campaigns/{$campaign->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function testUserCannotAccessOtherOrganizationCampaigns(): void
    {
        $otherOrganization = Organization::factory()->create();
        $campaign = Campaign::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->get("/main/{$this->organization->id}/campaigns/{$campaign->id}");

        $response->assertStatus(403);
    }
}

