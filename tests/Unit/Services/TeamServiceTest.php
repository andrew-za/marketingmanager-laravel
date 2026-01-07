<?php

namespace Tests\Unit\Services;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Services\Organization\TeamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeamService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TeamService();
    }

    public function testGetTeamMembers(): void
    {
        $organization = Organization::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $organization->users()->attach($user1->id, ['role_id' => 1]);
        $organization->users()->attach($user2->id, ['role_id' => 2]);

        $members = $this->service->getTeamMembers($organization);

        $this->assertCount(2, $members);
        $this->assertTrue($members->contains($user1));
        $this->assertTrue($members->contains($user2));
    }

    public function testAddTeamMember(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Client']);

        $this->service->addTeamMember($organization, $user->id, $role->id);

        $this->assertTrue($organization->users()->where('users.id', $user->id)->exists());
    }

    public function testRemoveTeamMember(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Client']);

        $organization->users()->attach($user->id, ['role_id' => $role->id]);
        
        $this->service->removeTeamMember($organization, $user->id);

        $this->assertFalse($organization->users()->where('users.id', $user->id)->exists());
    }

    public function testUpdateTeamMemberRole(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => 'Client']);
        $role2 = Role::factory()->create(['name' => 'Admin']);

        $organization->users()->attach($user->id, ['role_id' => $role1->id]);
        
        $this->service->updateTeamMemberRole($organization, $user->id, $role2->id);

        $pivot = $organization->users()->where('users.id', $user->id)->first()->pivot;
        $this->assertEquals($role2->id, $pivot->role_id);
    }

    public function testGetAvailableRoles(): void
    {
        Role::factory()->create(['name' => 'Client']);
        Role::factory()->create(['name' => 'Admin']);
        Role::factory()->create(['name' => 'Other']);

        $roles = $this->service->getAvailableRoles();

        $this->assertCount(2, $roles);
        $this->assertTrue($roles->pluck('name')->contains('Client'));
        $this->assertTrue($roles->pluck('name')->contains('Admin'));
    }
}

