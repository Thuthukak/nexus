<?php

declare(strict_types=1);

namespace Modules\Events\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EventsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'events.view',
            'events.manage',
            'events.delete',
            'events.orders' 
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::findByName('Super Admin', 'web');
        $admin->givePermissionTo($permissions);

        Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web'])
            ->givePermissionTo(['events.view', 'events.orders']);

        $this->command->info('Events permissions seeded.');
    }
}
