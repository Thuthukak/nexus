<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Only used for LOCAL DEVELOPMENT.
     * The wizard creates the real Super Admin in step 6.
     * Never call this from DatabaseSeeder directly.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@nexus.local'],
            [
                'name'              => 'Nexus Admin',
                'password'          => Hash::make('Admin@12345'),
                'guard'             => 'web',    // always 'web' for staff
                'portal_access'     => false,
                'email_verified_at' => now(),
                'is_active'         => true,
            ]
        );

        $user->assignRole('Super Admin');

        $this->command?->info('Dev Super Admin: admin@nexus.local / Admin@12345');
    }
}
