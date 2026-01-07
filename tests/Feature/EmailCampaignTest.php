<?php

namespace Tests\Feature;

use App\Models\ContactList;
use App\Models\EmailCampaign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCampaignTest extends TestCase
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

    public function testUserCanCreateEmailCampaign(): void
    {
        $contactList = ContactList::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->post("/main/{$this->organization->id}/email-campaigns", [
            'name' => 'Test Email Campaign',
            'subject' => 'Test Subject',
            'contact_list_id' => $contactList->id,
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('email_campaigns', [
            'name' => 'Test Email Campaign',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function testUserCanViewEmailCampaigns(): void
    {
        EmailCampaign::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get("/main/{$this->organization->id}/email-campaigns");

        $response->assertStatus(200);
    }

    public function testUserCanSendEmailCampaign(): void
    {
        $campaign = EmailCampaign::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        $response = $this->post("/main/{$this->organization->id}/email-campaigns/{$campaign->id}/send");

        $response->assertRedirect();
        $this->assertDatabaseHas('email_campaigns', [
            'id' => $campaign->id,
            'status' => 'sending',
        ]);
    }
}

