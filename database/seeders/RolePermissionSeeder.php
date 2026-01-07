<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPermissions();
        $this->createRoles();
        $this->assignPermissionsToRoles();
    }

    /**
     * Create all permissions
     */
    protected function createPermissions(): void
    {
        $permissions = [
            // Campaign permissions
            ['name' => 'campaigns.view', 'description' => 'View campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.create', 'description' => 'Create campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.update', 'description' => 'Update campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.delete', 'description' => 'Delete campaigns', 'module' => 'campaigns'],
            ['name' => 'campaigns.publish', 'description' => 'Publish campaigns', 'module' => 'campaigns'],

            // Content permissions
            ['name' => 'content.view', 'description' => 'View content', 'module' => 'content'],
            ['name' => 'content.create', 'description' => 'Create content', 'module' => 'content'],
            ['name' => 'content.update', 'description' => 'Update content', 'module' => 'content'],
            ['name' => 'content.delete', 'description' => 'Delete content', 'module' => 'content'],
            ['name' => 'content.approve', 'description' => 'Approve content', 'module' => 'content'],

            // Brand permissions
            ['name' => 'brands.view', 'description' => 'View brands', 'module' => 'brands'],
            ['name' => 'brands.create', 'description' => 'Create brands', 'module' => 'brands'],
            ['name' => 'brands.update', 'description' => 'Update brands', 'module' => 'brands'],
            ['name' => 'brands.delete', 'description' => 'Delete brands', 'module' => 'brands'],

            // Product permissions
            ['name' => 'products.view', 'description' => 'View products', 'module' => 'products'],
            ['name' => 'products.create', 'description' => 'Create products', 'module' => 'products'],
            ['name' => 'products.update', 'description' => 'Update products', 'module' => 'products'],
            ['name' => 'products.delete', 'description' => 'Delete products', 'module' => 'products'],

            // Organization permissions
            ['name' => 'organizations.view', 'description' => 'View organizations', 'module' => 'organizations'],
            ['name' => 'organizations.update', 'description' => 'Update organizations', 'module' => 'organizations'],
            ['name' => 'organizations.delete', 'description' => 'Delete organizations', 'module' => 'organizations'],
            ['name' => 'organizations.manage_users', 'description' => 'Manage organization users', 'module' => 'organizations'],

            // Channel permissions
            ['name' => 'channels.view', 'description' => 'View channels', 'module' => 'channels'],
            ['name' => 'channels.create', 'description' => 'Create channels', 'module' => 'channels'],
            ['name' => 'channels.update', 'description' => 'Update channels', 'module' => 'channels'],
            ['name' => 'channels.delete', 'description' => 'Delete channels', 'module' => 'channels'],

            // Email Campaign permissions
            ['name' => 'email_campaigns.view', 'description' => 'View email campaigns', 'module' => 'email_campaigns'],
            ['name' => 'email_campaigns.create', 'description' => 'Create email campaigns', 'module' => 'email_campaigns'],
            ['name' => 'email_campaigns.update', 'description' => 'Update email campaigns', 'module' => 'email_campaigns'],
            ['name' => 'email_campaigns.delete', 'description' => 'Delete email campaigns', 'module' => 'email_campaigns'],
            ['name' => 'email_campaigns.send', 'description' => 'Send email campaigns', 'module' => 'email_campaigns'],

            // Task permissions
            ['name' => 'tasks.view', 'description' => 'View tasks', 'module' => 'tasks'],
            ['name' => 'tasks.create', 'description' => 'Create tasks', 'module' => 'tasks'],
            ['name' => 'tasks.update', 'description' => 'Update tasks', 'module' => 'tasks'],
            ['name' => 'tasks.delete', 'description' => 'Delete tasks', 'module' => 'tasks'],
            ['name' => 'tasks.assign', 'description' => 'Assign tasks', 'module' => 'tasks'],

            // Project permissions
            ['name' => 'projects.view', 'description' => 'View projects', 'module' => 'projects'],
            ['name' => 'projects.create', 'description' => 'Create projects', 'module' => 'projects'],
            ['name' => 'projects.update', 'description' => 'Update projects', 'module' => 'projects'],
            ['name' => 'projects.delete', 'description' => 'Delete projects', 'module' => 'projects'],

            // Report permissions
            ['name' => 'reports.view', 'description' => 'View reports', 'module' => 'reports'],
            ['name' => 'reports.create', 'description' => 'Create reports', 'module' => 'reports'],
            ['name' => 'reports.update', 'description' => 'Update reports', 'module' => 'reports'],
            ['name' => 'reports.delete', 'description' => 'Delete reports', 'module' => 'reports'],
            ['name' => 'reports.share', 'description' => 'Share reports', 'module' => 'reports'],

            // Analytics permissions
            ['name' => 'analytics.view', 'description' => 'View analytics', 'module' => 'analytics'],

            // AI permissions
            ['name' => 'ai.generate_content', 'description' => 'Generate AI content', 'module' => 'ai'],
            ['name' => 'ai.generate_images', 'description' => 'Generate AI images', 'module' => 'ai'],

            // Settings permissions
            ['name' => 'settings.view', 'description' => 'View settings', 'module' => 'settings'],
            ['name' => 'settings.update', 'description' => 'Update settings', 'module' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    /**
     * Create default roles
     */
    protected function createRoles(): void
    {
        $roles = [
            ['name' => 'super_admin', 'description' => 'Super Administrator', 'level' => 100],
            ['name' => 'admin', 'description' => 'Administrator', 'level' => 90],
            ['name' => 'editor', 'description' => 'Editor', 'level' => 50],
            ['name' => 'viewer', 'description' => 'Viewer', 'level' => 10],
            ['name' => 'agency_admin', 'description' => 'Agency Administrator', 'level' => 80],
            ['name' => 'agency_member', 'description' => 'Agency Member', 'level' => 40],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }

    /**
     * Assign permissions to roles
     */
    protected function assignPermissionsToRoles(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $editor = Role::where('name', 'editor')->first();
        $viewer = Role::where('name', 'viewer')->first();
        $agencyAdmin = Role::where('name', 'agency_admin')->first();
        $agencyMember = Role::where('name', 'agency_member')->first();

        // Super Admin gets all permissions
        $superAdmin->givePermissionTo(Permission::all());

        // Admin gets most permissions except super admin specific ones
        $admin->givePermissionTo([
            'campaigns.view', 'campaigns.create', 'campaigns.update', 'campaigns.delete', 'campaigns.publish',
            'content.view', 'content.create', 'content.update', 'content.delete', 'content.approve',
            'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'organizations.view', 'organizations.update', 'organizations.manage_users',
            'channels.view', 'channels.create', 'channels.update', 'channels.delete',
            'email_campaigns.view', 'email_campaigns.create', 'email_campaigns.update', 'email_campaigns.delete', 'email_campaigns.send',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete', 'tasks.assign',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'reports.view', 'reports.create', 'reports.update', 'reports.delete', 'reports.share',
            'analytics.view',
            'ai.generate_content', 'ai.generate_images',
            'settings.view', 'settings.update',
        ]);

        // Editor can create and update but not delete
        $editor->givePermissionTo([
            'campaigns.view', 'campaigns.create', 'campaigns.update', 'campaigns.publish',
            'content.view', 'content.create', 'content.update',
            'brands.view', 'brands.create', 'brands.update',
            'products.view', 'products.create', 'products.update',
            'channels.view', 'channels.create', 'channels.update',
            'email_campaigns.view', 'email_campaigns.create', 'email_campaigns.update', 'email_campaigns.send',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.assign',
            'projects.view', 'projects.create', 'projects.update',
            'reports.view', 'reports.create', 'reports.update',
            'analytics.view',
            'ai.generate_content', 'ai.generate_images',
        ]);

        // Viewer can only view
        $viewer->givePermissionTo([
            'campaigns.view',
            'content.view',
            'brands.view',
            'products.view',
            'channels.view',
            'email_campaigns.view',
            'tasks.view',
            'projects.view',
            'reports.view',
            'analytics.view',
        ]);

        // Agency Admin gets similar permissions to admin
        $agencyAdmin->givePermissionTo([
            'campaigns.view', 'campaigns.create', 'campaigns.update', 'campaigns.delete', 'campaigns.publish',
            'content.view', 'content.create', 'content.update', 'content.delete', 'content.approve',
            'brands.view', 'brands.create', 'brands.update', 'brands.delete',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'channels.view', 'channels.create', 'channels.update', 'channels.delete',
            'email_campaigns.view', 'email_campaigns.create', 'email_campaigns.update', 'email_campaigns.delete', 'email_campaigns.send',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete', 'tasks.assign',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'reports.view', 'reports.create', 'reports.update', 'reports.delete', 'reports.share',
            'analytics.view',
            'ai.generate_content', 'ai.generate_images',
        ]);

        // Agency Member gets editor-like permissions
        $agencyMember->givePermissionTo([
            'campaigns.view', 'campaigns.create', 'campaigns.update', 'campaigns.publish',
            'content.view', 'content.create', 'content.update',
            'brands.view', 'brands.create', 'brands.update',
            'products.view', 'products.create', 'products.update',
            'channels.view', 'channels.create', 'channels.update',
            'email_campaigns.view', 'email_campaigns.create', 'email_campaigns.update', 'email_campaigns.send',
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.assign',
            'projects.view', 'projects.create', 'projects.update',
            'reports.view', 'reports.create', 'reports.update',
            'analytics.view',
            'ai.generate_content', 'ai.generate_images',
        ]);
    }
}


