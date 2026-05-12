<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Super Admin — wildcard, handled by Spatie's super-admin gate
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web']
        );

        // Admin — all core permissions
        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web']
        );
        $admin->syncPermissions(Permission::where('guard_name', 'web')->get());

        // Manager — view + limited core
        $manager = Role::firstOrCreate(
            ['name' => 'Manager', 'guard_name' => 'web']
        );
        $manager->syncPermissions(
            Permission::where('guard_name', 'web')
                ->where('name', 'like', '%.view')
                ->orWhere('name', 'like', '%.create')
                ->get()
        );

        // Staff — view only on core
        $staff = Role::firstOrCreate(
            ['name' => 'Staff', 'guard_name' => 'web']
        );
        $staff->syncPermissions(
            Permission::where('guard_name', 'web')
                ->where('name', 'like', '%.view')
                ->get()
        );

        // Read Only
        Role::firstOrCreate(
            ['name' => 'Read Only', 'guard_name' => 'web']
        );

        // Customer roles (portal guard)
        Role::firstOrCreate(
            ['name' => 'Customer Admin', 'guard_name' => 'web']
        );
        Role::firstOrCreate(
            ['name' => 'Customer User', 'guard_name' => 'web']
        );

        $this->command->info('Roles seeded.');
    }
}
