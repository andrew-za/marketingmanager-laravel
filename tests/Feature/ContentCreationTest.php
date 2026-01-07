<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Organization;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->brand = Brand::factory()->create(['organization_id' => $this->organization->id]);
        $this->user->organizations()->attach($this->organization->id, ['role_id' => 1]);
        
        $this->actingAs($this->user);
    }

    public function testUserCanCreateContent(): void
    {
        $campaign = Campaign::factory()->create([
            'organization_id' => $this->organization->id,
            'brand_id' => $this->brand->id,
        ]);

        $response = $this->post("/main/{$this->organization->id}/scheduled-posts", [
            'campaign_id' => $campaign->id,
            'content' => 'Test Content',
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scheduled_posts', [
            'content' => 'Test Content',
            'campaign_id' => $campaign->id,
        ]);
    }

    public function testUserCanUpdateContent(): void
    {
        $post = ScheduledPost::factory()->create([
            'organization_id' => $this->organization->id,
            'campaign_id' => Campaign::factory()->create(['organization_id' => $this->organization->id]),
        ]);

        $response = $this->put("/main/{$this->organization->id}/scheduled-posts/{$post->id}", [
            'content' => 'Updated Content',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scheduled_posts', [
            'id' => $post->id,
            'content' => 'Updated Content',
        ]);
    }

    public function testUserCanSubmitContentForApproval(): void
    {
        $post = ScheduledPost::factory()->create([
            'organization_id' => $this->organization->id,
            'campaign_id' => Campaign::factory()->create(['organization_id' => $this->organization->id]),
            'status' => 'draft',
        ]);

        $response = $this->post("/main/{$this->organization->id}/scheduled-posts/{$post->id}/submit");

        $response->assertRedirect();
        $this->assertDatabaseHas('scheduled_posts', [
            'id' => $post->id,
            'status' => 'pending_approval',
        ]);
    }
}

