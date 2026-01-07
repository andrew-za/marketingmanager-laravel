<?php

namespace Tests\Unit\Services;

use App\Models\Campaign;
use App\Models\Organization;
use App\Models\User;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Services\Campaign\CampaignNotificationService;
use App\Services\Campaign\CampaignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CampaignServiceTest extends TestCase
{
    use RefreshDatabase;

    private CampaignService $service;
    private $repositoryMock;
    private $notificationServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repositoryMock = Mockery::mock(CampaignRepositoryInterface::class);
        $this->notificationServiceMock = Mockery::mock(CampaignNotificationService::class);
        
        $this->service = new CampaignService(
            $this->repositoryMock,
            $this->notificationServiceMock
        );
    }

    public function testCreateCampaign(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, ['role_id' => 1]);
        
        $campaignData = [
            'name' => 'Test Campaign',
            'description' => 'Test Description',
            'status' => 'draft',
        ];

        $campaign = Campaign::factory()->make($campaignData);
        
        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($campaign);

        $this->notificationServiceMock
            ->shouldReceive('notifyCampaignCreated')
            ->once()
            ->with($campaign);

        $result = $this->service->createCampaign($campaignData, $user);

        $this->assertInstanceOf(Campaign::class, $result);
    }

    public function testUpdateCampaign(): void
    {
        $campaign = Campaign::factory()->create();
        $updateData = ['name' => 'Updated Campaign Name'];

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($campaign, $updateData)
            ->andReturn($campaign);

        $this->notificationServiceMock
            ->shouldReceive('notifyCampaignUpdated')
            ->once()
            ->with($campaign);

        $result = $this->service->updateCampaign($campaign, $updateData);

        $this->assertInstanceOf(Campaign::class, $result);
    }

    public function testDeleteCampaign(): void
    {
        $campaign = Campaign::factory()->create();

        $this->repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($campaign)
            ->andReturn(true);

        $result = $this->service->deleteCampaign($campaign);

        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

