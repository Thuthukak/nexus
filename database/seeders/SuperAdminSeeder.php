<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@nexus.local'],
            [
                'name'              => 'Nexus Admin',
                'password'          => Hash::make('password'),
                'guard'             => 'internal',
                'portal_access'     => false,
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('Super Admin');

        $this->command->info("Super Admin created: admin@nexus.local / password");
    }
}
