<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiGenerationTest extends TestCase
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

    public function testUserCanGenerateSocialMediaPost(): void
    {
        $response = $this->post("/main/{$this->organization->id}/ai/generate-content", [
            'type' => 'social_media_post',
            'platform' => 'facebook',
            'topic' => 'Product Launch',
            'tone' => 'professional',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'content',
            'usage',
        ]);
    }

    public function testUserCanGenerateEmailTemplate(): void
    {
        $response = $this->post("/main/{$this->organization->id}/ai/generate-email", [
            'subject' => 'Welcome Email',
            'purpose' => 'onboarding',
            'tone' => 'friendly',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'subject',
            'body',
            'usage',
        ]);
    }

    public function testUserCanGenerateImage(): void
    {
        $response = $this->post("/main/{$this->organization->id}/ai/generate-image", [
            'prompt' => 'A modern office space',
            'style' => 'realistic',
            'size' => '1024x1024',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'image_url',
            'usage',
        ]);
    }

    public function testAiGenerationTracksUsage(): void
    {
        $this->post("/main/{$this->organization->id}/ai/generate-content", [
            'type' => 'social_media_post',
            'platform' => 'facebook',
            'topic' => 'Test',
        ]);

        $this->assertDatabaseHas('ai_usage_logs', [
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);
    }
}

