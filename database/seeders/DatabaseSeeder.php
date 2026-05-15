<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Core
            ThemeSeeder::class,
            CorePermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,

            // Modules — run after roles exist
            \Modules\Financial\database\seeders\FinancialPermissionSeeder::class,
            \Modules\HR\database\seeders\HRPermissionSeeder::class,
            \Modules\Bookings\database\seeders\BookingsPermissionSeeder::class,
        ]);
    }
}
