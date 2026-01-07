<?php

namespace Tests\Unit\Repositories;

use App\Models\Campaign;
use App\Models\Organization;
use App\Repositories\Eloquent\CampaignRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CampaignRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CampaignRepository();
    }

    public function testFindReturnsCampaign(): void
    {
        $campaign = Campaign::factory()->create();

        $result = $this->repository->find($campaign->id);

        $this->assertInstanceOf(Campaign::class, $result);
        $this->assertEquals($campaign->id, $result->id);
    }

    public function testFindReturnsNullWhenNotFound(): void
    {
        $result = $this->repository->find(999);

        $this->assertNull($result);
    }

    public function testCreateCampaign(): void
    {
        $organization = Organization::factory()->create();
        $data = [
            'organization_id' => $organization->id,
            'name' => 'Test Campaign',
            'status' => 'draft',
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Campaign::class, $result);
        $this->assertEquals('Test Campaign', $result->name);
        $this->assertDatabaseHas('campaigns', [
            'name' => 'Test Campaign',
        ]);
    }

    public function testUpdateCampaign(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Original Name']);

        $result = $this->repository->update($campaign, ['name' => 'Updated Name']);

        $this->assertInstanceOf(Campaign::class, $result);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Updated Name',
        ]);
    }

    public function testDeleteCampaign(): void
    {
        $campaign = Campaign::factory()->create();

        $result = $this->repository->delete($campaign);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('campaigns', [
            'id' => $campaign->id,
        ]);
    }

    public function testFindByOrganization(): void
    {
        $organization1 = Organization::factory()->create();
        $organization2 = Organization::factory()->create();

        Campaign::factory()->count(3)->create(['organization_id' => $organization1->id]);
        Campaign::factory()->count(2)->create(['organization_id' => $organization2->id]);

        $result = $this->repository->findByOrganization($organization1->id);

        $this->assertCount(3, $result);
        $result->each(function ($campaign) use ($organization1) {
            $this->assertEquals($organization1->id, $campaign->organization_id);
        });
    }
}

