<?php

namespace Tests\Feature\Campaign;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_campaign(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/campaigns', [
                'name' => 'Test Campaign',
                'start_date' => now()->addDay()->toDateString(),
                'budget' => 1000,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('campaigns', [
            'name' => 'Test Campaign',
            'organization_id' => $organization->id,
        ]);
    }
}

