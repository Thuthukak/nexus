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
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Use firstOrCreate — roles are guaranteed to exist (WizardSeeder
        // Phase 1 creates them, RoleSeeder creates them for artisan db:seed)
        $rolePermissions = [
            'Super Admin' => $permissions,
            'Admin'   => [
                'core.users.view',
                'core.users.create',
                'core.users.edit',
                'core.users.delete',
                'core.roles.manage',
                'core.settings.manage',
                'core.modules.manage',
                'core.activity.view',
            ],
            'Manager' => [
                'core.users.view',
                'core.activity.view',
                'core.settings.manage',
            ],
            'Staff' => [
                'core.users.view',
                'core.activity.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('name', $roleName)
                        ->where('guard_name', 'web')
                        ->first();

            if ($role) {
                $role->givePermissionTo($perms);
            }
        }

        $this->command?->info('Core permissions seeded.');
    }
}
