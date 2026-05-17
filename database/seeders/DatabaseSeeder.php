<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Production-safe Seeders (Always Run)
        $this->call([
            // Core platform
            ThemeSeeder::class,
            CorePermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,

            // Module permissions
            \Modules\Financial\database\seeders\FinancialPermissionSeeder::class,
            \Modules\HR\database\seeders\HRPermissionSeeder::class,
            \Modules\Bookings\database\seeders\BookingsPermissionSeeder::class,
        ]);

        // Development-only Seeders (Gated)
        if (app()->isLocal()) {
            $this->call([
                DevDataSeeder::class,
            ]);
        }
    }
}