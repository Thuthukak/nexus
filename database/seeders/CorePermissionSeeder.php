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
            // User management
            'core.users.view',
            'core.users.create',
            'core.users.edit',
            'core.users.delete',

            // Role management
            'core.roles.manage',

            // Settings
            'core.settings.manage',

            // Module manager
            'core.modules.manage',

            // Activity log
            'core.activity.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $this->command->info('Core permissions seeded.');
    }
}
