<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CorePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'core.users.view',
            'core.users.create',
            'core.users.edit',
            'core.users.delete',
            'core.roles.manage',
            'core.settings.manage',
            'core.modules.manage',
            'core.activity.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission, 
                'guard_name' => 'web'
            ]);
        }

        $superAdmin = Role::findByName('Super Admin', 'web');
        $superAdmin->givePermissionTo($permissions);

        $admin = Role::findByName('Admin', 'web');
        $admin->givePermissionTo([
            'core.users.view',
            'core.roles.manage',
            'core.settings.manage',
            'core.modules.manage',
            'core.activity.view',
        ]);

        $manager = Role::findByName('Manager', 'web');
        $manager->givePermissionTo([
            'core.users.view',
            'core.roles.manage',
            'core.settings.manage',
            'core.activity.view',
        ]);

        $staff = Role::findByName('Staff', 'web');
        $staff->givePermissionTo([
            'core.users.view',
            'core.activity.view',
        ]);

        $this->command->info('Core permissions seeded.');

        $this->command->info('Core permissions seeded.');
    }
}
