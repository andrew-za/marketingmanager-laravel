<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function testOrganizationHasManyBrands(): void
    {
        $organization = Organization::factory()->create();
        Brand::factory()->count(3)->create(['organization_id' => $organization->id]);

        $this->assertCount(3, $organization->brands);
    }

    public function testOrganizationHasManyCampaigns(): void
    {
        $organization = Organization::factory()->create();
        Campaign::factory()->count(5)->create(['organization_id' => $organization->id]);

        $this->assertCount(5, $organization->campaigns);
    }

    public function testOrganizationBelongsToManyUsers(): void
    {
        $organization = Organization::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $organization->users()->attach($user1->id, ['role_id' => 1]);
        $organization->users()->attach($user2->id, ['role_id' => 2]);

        $this->assertCount(2, $organization->users);
        $this->assertTrue($organization->users->contains($user1));
        $this->assertTrue($organization->users->contains($user2));
    }

    public function testOrganizationTrialEndsAtIsCasted(): void
    {
        $organization = Organization::factory()->create([
            'trial_ends_at' => '2024-12-31 23:59:59',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $organization->trial_ends_at);
    }

    public function testOrganizationSupportedLocalesIsCasted(): void
    {
        $organization = Organization::factory()->create([
            'supported_locales' => ['en', 'es', 'fr'],
        ]);

        $this->assertIsArray($organization->supported_locales);
        $this->assertEquals(['en', 'es', 'fr'], $organization->supported_locales);
    }
}

