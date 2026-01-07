<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\ScheduledPost;
use App\Models\SocialConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialPublishingTest extends TestCase
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

    public function testUserCanSchedulePost(): void
    {
        $connection = SocialConnection::factory()->create([
            'organization_id' => $this->organization->id,
            'platform' => 'facebook',
            'status' => 'connected',
        ]);

        $response = $this->post("/main/{$this->organization->id}/scheduled-posts", [
            'social_connection_id' => $connection->id,
            'content' => 'Test post content',
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scheduled_posts', [
            'social_connection_id' => $connection->id,
            'content' => 'Test post content',
        ]);
    }

    public function testUserCanPublishPostImmediately(): void
    {
        $connection = SocialConnection::factory()->create([
            'organization_id' => $this->organization->id,
            'platform' => 'facebook',
            'status' => 'connected',
        ]);

        $response = $this->post("/main/{$this->organization->id}/scheduled-posts/publish", [
            'social_connection_id' => $connection->id,
            'content' => 'Immediate post',
        ]);

        $response->assertStatus(200);
    }

    public function testUserCannotPublishWithoutConnection(): void
    {
        $response = $this->post("/main/{$this->organization->id}/scheduled-posts/publish", [
            'social_connection_id' => 999,
            'content' => 'Test post',
        ]);

        $response->assertSessionHasErrors();
    }
}

