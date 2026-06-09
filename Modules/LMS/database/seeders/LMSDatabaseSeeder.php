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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign to default roles
        $admin = Role::findByName('Super Admin', 'web');
        $admin->givePermissionTo($permissions);

        $manager = Role::findByName('Manager', 'web');
        $manager->givePermissionTo([
            'lms.course.view',
            'lms.course.create',
            'lms.course.edit',
            'lms.course.delete',
        ]);

        $staff = Role::findByName('Staff', 'web');
        $staff->givePermissionTo([
            'lms.course.view',
            'lms.course.create',
            'lms.course.edit',
            'lms.course.delete',
        ]);

        $this->command->info('lms permissions seeded.');
    }
}
