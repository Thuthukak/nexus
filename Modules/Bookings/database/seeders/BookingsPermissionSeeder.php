<?php

declare(strict_types=1);

namespace Modules\Bookings\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BookingsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'bookings.bookings.view',
            'bookings.bookings.create',
            'bookings.bookings.edit',
            'bookings.bookings.delete',
            'bookings.bookings.confirm',
            'bookings.resources.manage',
            'bookings.services.manage',
            'bookings.reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::findByName('Admin', 'web');
        $admin->givePermissionTo($permissions);

        $manager = Role::findByName('Manager', 'web');
        $manager->givePermissionTo([
            'bookings.bookings.view',
            'bookings.bookings.create',
            'bookings.bookings.confirm',
            'bookings.reports.view',
        ]);

        $staff = Role::findByName('Staff', 'web');
        $staff->givePermissionTo([
            'bookings.bookings.view',
            'bookings.bookings.create',
        ]);

        $this->command->info('Bookings permissions seeded.');
    }
}
