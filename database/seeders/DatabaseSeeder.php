<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->createRoles();
        $this->createPermissions();
    }

    private function createRoles(): void
    {
        $roles = [
            ['name' => 'super_admin', 'description' => 'Super Administrator'],
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'editor', 'description' => 'Editor'],
            ['name' => 'viewer', 'description' => 'Viewer'],
            ['name' => 'agency_admin', 'description' => 'Agency Administrator'],
            ['name' => 'agency_member', 'description' => 'Agency Member'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }

    private function createPermissions(): void
    {
        $permissions = [
            ['name' => 'campaigns.view', 'description' => 'View campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.create', 'description' => 'Create campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.update', 'description' => 'Update campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.delete', 'description' => 'Delete campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.publish', 'description' => 'Publish campaigns', 'module' => 'campaigns'],
            ['name' => 'content.view', 'description' => 'View content', 'module' => 'content'],
            ['name' => 'content.create', 'description' => 'Create content', 'module' => 'content'],
            ['name' => 'content.update', 'description' => 'Update content', 'module' => 'content'],
            ['name' => 'content.delete', 'description' => 'Delete content', 'module' => 'content'],
            ['name' => 'content.approve', 'description' => 'Approve content', 'module' => 'content'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'description' => $permission['description'],
                    'module' => $permission['module'],
                ]
            );
        }
    }
}


