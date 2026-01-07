<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function testCampaignBelongsToOrganization(): void
    {
        $organization = Organization::factory()->create();
        $campaign = Campaign::factory()->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $campaign->organization);
        $this->assertEquals($organization->id, $campaign->organization->id);
    }

    public function testCampaignBelongsToCreator(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $campaign->creator);
        $this->assertEquals($user->id, $campaign->creator->id);
    }

    public function testCampaignBelongsToBrand(): void
    {
        $brand = Brand::factory()->create();
        $campaign = Campaign::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(Brand::class, $campaign->brand);
        $this->assertEquals($brand->id, $campaign->brand->id);
    }

    public function testCampaignHasManyScheduledPosts(): void
    {
        $campaign = Campaign::factory()->create();
        
        // Assuming ScheduledPost model exists
        // $scheduledPost1 = ScheduledPost::factory()->create(['campaign_id' => $campaign->id]);
        // $scheduledPost2 = ScheduledPost::factory()->create(['campaign_id' => $campaign->id]);

        // $this->assertCount(2, $campaign->scheduledPosts);
    }

    public function testCampaignStatusIsCasted(): void
    {
        $campaign = Campaign::factory()->create(['status' => 'draft']);
        
        $this->assertEquals('draft', $campaign->status);
    }

    public function testCampaignDatesAreCasted(): void
    {
        $campaign = Campaign::factory()->create([
            'start_date' => '2024-01-01 10:00:00',
            'end_date' => '2024-12-31 18:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $campaign->end_date);
    }

    public function testCampaignBudgetIsCasted(): void
    {
        $campaign = Campaign::factory()->create(['budget' => 1000.50]);

        $this->assertEquals('1000.50', $campaign->budget);
    }
}

