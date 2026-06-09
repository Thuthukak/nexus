<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Phase 1: Create roles (empty) so permission seeders
        // can safely reference role names
        $this->call(RoleSeeder::class);

        // Phase 2: Theme defaults
        $this->call(ThemeSeeder::class);

        // Phase 3: All permissions
        $this->call(CorePermissionSeeder::class);
        $this->callIfExists(\Modules\Financial\database\seeders\FinancialPermissionSeeder::class);
        $this->callIfExists(\Modules\HR\database\seeders\HRPermissionSeeder::class);
        $this->callIfExists(\Modules\Bookings\database\seeders\BookingsPermissionSeeder::class);
        $this->callIfExists(\Database\Seeders\LmsPermissionsSeeder::class);
        $this->callIfExists(\Database\Seeders\EventsPermissionsSeeder::class);
        $this->callIfExists(\Modules\LMS\database\seeders\LMSDatabaseSeeder::class);

        // Phase 4: Re-run RoleSeeder to sync permissions now that
        // all permissions exist (firstOrCreate — no duplicates)
        $this->call(RoleSeeder::class);

        // Local dev only — creates admin@nexus.local test account
        if (app()->isLocal()) {
            $this->call(SuperAdminSeeder::class);
            $this->callIfExists(DevDataSeeder::class);
        }
    }

    private function callIfExists(string $class): void
    {
        if (class_exists($class)) {
            $this->call($class);
        } else {
            $this->command?->warn("Seeder not found (skipping): {$class}");
        }
    }
}
