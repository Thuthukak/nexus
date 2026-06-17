<?php

declare(strict_types=1);

namespace Modules\HR\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class HRPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
            'hr.employees.manage',
            'hr.departments.manage',
            'hr.leave.view',
            'hr.leave.create',
            'hr.leave.approve',
            'hr.documents.manage',
            'hr.reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign to default roles
        $superAdmin = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
        $superAdmin->givePermissionTo($permissions);

        $admin = Role::where('name', 'Admin')->where('guard_name', 'web')->first();
        $admin->givePermissionTo([
            'hr.employees.view',
            'hr.employees.manage',
            'hr.departments.manage',
            'hr.leave.view',
            'hr.leave.approve',
            'hr.documents.manage',
            'hr.reports.view',
        ]);

        $manager = Role::where('name', 'Manager')->where('guard_name', 'web')->first();
        $manager->givePermissionTo([
            'hr.employees.view',
            'hr.leave.view',
            'hr.leave.approve',
            'hr.reports.view',
        ]);

        $staff = Role::where('name', 'Staff')->where('guard_name', 'web')->first();
        $staff->givePermissionTo([
            'hr.employees.view',
            'hr.leave.view',
            'hr.leave.create',
        ]);

        $this->command?->info('HR permissions seeded.');
    }
}
