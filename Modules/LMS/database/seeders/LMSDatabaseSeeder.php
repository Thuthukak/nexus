<?php

declare(strict_types=1);

namespace Modules\LMS\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LMSDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'lms.course.view',
            'lms.course.create',
            'lms.course.edit',
            'lms.course.delete',
            'lms.course.manage',
            'lms.cohorts.view',
            'lms.cohorts.manage',
            'lms.assignments.view',
            'lms.assignments.manage',
            'lms.grades.manage',
            'lms.reports.view',
            'lms.student.access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'Super Admin' => $permissions,
            'Admin'       => $permissions,
            'Manager'     => [
                'lms.course.view',
                'lms.cohorts.view',
                'lms.cohorts.manage',
                'lms.assignments.view',
                'lms.reports.view',
                'lms.student.access',
            ],
            'Staff' => [
                'lms.course.view',
                'lms.cohorts.view',
                'lms.student.access',
            ],
            'Teacher' => [
                'lms.course.view',
                'lms.course.manage',
                'lms.cohorts.view',
                'lms.cohorts.manage',
                'lms.assignments.view',
                'lms.assignments.manage',
                'lms.grades.manage',
                'lms.reports.view',
                'lms.student.access',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo(
                    Permission::whereIn('name', $perms)->where('guard_name', 'web')->get()
                );
            }
        }

        $this->command?->info('LMS permissions seeded.');
    }
}
