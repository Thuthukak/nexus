<?php
namespace Modules\LMS\Database\Seeders;

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

        $admin = Role::findByName('Super Admin', 'web');
        $admin->givePermissionTo($permissions);

        $manager = Role::findByName('Manager', 'web');
        $manager->givePermissionTo([
            'lms.course.view',
            'lms.cohorts.view',
            'lms.cohorts.manage',
            'lms.reports.view',
            'lms.student.access',
        ]);

        $staff = Role::findByName('Staff', 'web');
        $staff->givePermissionTo([
            'lms.course.view',
            'lms.course.manage',
            'lms.cohorts.view',
            'lms.grades.manage',
            'lms.reports.view',
            'lms.student.access',
        ]);

        $this->command->info('lms permissions seeded.');
    }
}