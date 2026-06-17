<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear permission cache before seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web'; // all internal staff use 'web' guard

        // ── Super Admin ────────────────────────────────────────
        // Spatie handles wildcard via gate — no permissions to sync
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);

        // ── Admin ──────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => $guard]);
        $admin->syncPermissions(
            Permission::where('guard_name', $guard)->get()
        );

        // ── Manager ────────────────────────────────────────────
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => $guard]);
        $manager->syncPermissions(
            Permission::where('guard_name', $guard)
                ->where(function ($q) {
                    $q->where('name', 'like', '%.view')
                      ->orWhere('name', 'like', '%.create');
                })
                ->get()
        );

        // ── Staff ──────────────────────────────────────────────
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => $guard]);
        $staff->syncPermissions(
            Permission::where('guard_name', $guard)
                ->where('name', 'like', '%.view')
                ->get()
        );

        // ── Teacher (LMS) ──────────────────────────────────────
        $teacher = Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => $guard]);
        $teacher->syncPermissions(
            Permission::where('guard_name', $guard)
                ->where('name', 'like', 'lms.%')
                ->get()
        );

        // ── Read Only ──────────────────────────────────────────
        $readOnly = Role::firstOrCreate(['name' => 'Read Only', 'guard_name' => $guard]);
        $readOnly->syncPermissions(
            Permission::where('guard_name', $guard)
                ->where('name', 'like', '%.view')
                ->get()
        );

        // ── Customer roles ─────────────────────────────────────
        // These use 'web' guard_name too — customers are in the same
        // users table, just differentiated by guard column value
        Role::firstOrCreate(['name' => 'Customer Admin', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'Customer User',  'guard_name' => $guard]);

        $this->command?->info('Roles seeded successfully.');
    }
}
