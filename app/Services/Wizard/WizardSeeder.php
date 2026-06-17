<?php

declare(strict_types=1);

namespace App\Services\Wizard;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Handles database seeding during the installation wizard.
 *
 * Order is critical:
 *   1. Roles created (empty — no permission sync)
 *   2. All permissions created (by individual seeders)
 *   3. Roles synced with permissions that now exist
 *
 * This avoids "role X does not exist" errors that occur when a
 * permission seeder tries to givePermissionTo() before RoleSeeder runs,
 * AND avoids empty role syncs that occur when RoleSeeder runs before
 * permissions exist.
 */
class WizardSeeder
{
    private const STAFF_GUARD = 'web';

    private const ROLES = [
        'Super Admin',
        'Admin',
        'Manager',
        'Teacher',
        'Staff',
        'Read Only',
        'Customer Admin',
        'Customer User',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Phase 1: Create all roles (empty) ────────────────
        // Do this FIRST so permission seeders can safely reference
        // role names without "role does not exist" errors.
        Log::info('WizardSeeder phase 1: creating roles');
        foreach (self::ROLES as $roleName) {
            Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => self::STAFF_GUARD,
            ]);
        }
        Log::info('Roles created: ' . implode(', ', self::ROLES));

        // ── Phase 2: Seed theme defaults ─────────────────────
        $this->callSeeder('ThemeSeeder');

        // ── Phase 3: Seed all permissions ────────────────────
        // Individual permission seeders may call givePermissionTo()
        // on roles — that's safe now because roles exist.
        Log::info('WizardSeeder phase 3: seeding permissions');
        $this->callSeeder('CorePermissionSeeder');

        // Module permission seeders
        $modulePermissionSeeders = [
            \Modules\Financial\database\seeders\FinancialPermissionSeeder::class,
            \Modules\HR\database\seeders\HRPermissionSeeder::class,
            \Modules\Bookings\database\seeders\BookingsPermissionSeeder::class,
            \Modules\LMS\database\seeders\LMSPermissionsSeeder::class,
            \Modules\Events\database\seeders\EventsPermissionsSeeder::class,
        ];

        foreach ($modulePermissionSeeders as $class) {
            $this->callSeederIfExists($class);
        }

        // ── Phase 4: Sync role permissions ───────────────────
        // Now that ALL permissions exist, do a final sync so each
        // role gets all the permissions it should have.
        Log::info('WizardSeeder phase 4: syncing role permissions');
        $this->syncRolePermissions();

        Log::info('WizardSeeder complete.');
    }

    private function syncRolePermissions(): void
    {
        $guard = self::STAFF_GUARD;

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Admin — all permissions
        $admin = Role::where('name', 'Admin')->where('guard_name', $guard)->first();
        if ($admin) {
            $admin->syncPermissions(Permission::where('guard_name', $guard)->get());
            Log::info('Admin synced: ' . $admin->permissions()->count() . ' permissions');
        }

        // Manager — view + create + manage
        $manager = Role::where('name', 'Manager')->where('guard_name', $guard)->first();
        if ($manager) {
            $manager->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%.view')
                          ->orWhere('name', 'like', '%.create')
                          ->orWhere('name', 'like', '%.manage');
                    })
                    ->get()
            );
        }

        // Staff — view only
        $staff = Role::where('name', 'Staff')->where('guard_name', $guard)->first();
        if ($staff) {
            $staff->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->where('name', 'like', '%.view')
                    ->get()
            );
        }

        // Teacher — LMS permissions only
        $teacher = Role::where('name', 'Teacher')->where('guard_name', $guard)->first();
        if ($teacher) {
            $teacher->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->where('name', 'like', 'lms.%')
                    ->get()
            );
        }

        // Read Only — view only (same as Staff)
        $readOnly = Role::where('name', 'Read Only')->where('guard_name', $guard)->first();
        if ($readOnly) {
            $readOnly->syncPermissions(
                Permission::where('guard_name', $guard)
                    ->where('name', 'like', '%.view')
                    ->get()
            );
        }

        // Super Admin, Customer Admin, Customer User — no explicit permissions
        // Super Admin is handled by Spatie gate wildcard
    }

    private function callSeeder(string $shortClass): void
    {
        $class = str_contains($shortClass, '\\')
            ? $shortClass
            : "Database\\Seeders\\{$shortClass}";

        Log::info("WizardSeeder: running {$class}");
        Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
        Log::info(Artisan::output());
    }

    private function callSeederIfExists(string $class): void
    {
        if (! class_exists($class)) {
            Log::info("WizardSeeder: {$class} not found — skipping");
            return;
        }
        $this->callSeeder($class);
    }
}
