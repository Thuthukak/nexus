<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstalledModulesSeeder extends Seeder
{
    /**
     * Central registry of installed modules.
     *
     * To ship an upgrade: bump the `version` value for the relevant module
     * below and re-run this seeder (php artisan db:seed --class=InstalledModulesSeeder).
     * Existing rows are matched on `name` and updated in place — nothing is duplicated,
     * and `is_enabled` / `is_licensed` / `enabled_at` / `licensed_at` are left untouched
     * on repeat runs so you don't accidentally re-lock or re-enable a module.
     * 
     * Also update version in the ModuleRegistryService::KNOWN_MODULES array to match.
     */
    protected array $modules = [
        [
            'name'        => 'Core',
            'alias'       => 'core',
            'version'     => '2.0.0',
            'is_core'     => true,
            'is_enabled'  => true,
            'is_licensed' => true,
            'metadata'    => [
                'alias'       => 'core',
                'order'       => 0,
                'is_core'     => true,
                'version'     => '1.0.0',
                'requires'    => [],
                'description' => 'Core platform — auth, users, roles, settings',
            ],
        ],
        [
            'name'        => 'Financial',
            'alias'       => 'financial',
            'version'     => '2.0.0',
            'is_core'     => false,
            'is_enabled'  => true,
            'is_licensed' => true,
            'metadata'    => [
                'alias'       => 'financial',
                'order'       => 10,
                'is_core'     => false,
                'version'     => '1.0.0',
                'requires'    => ['Core'],
                'description' => 'Invoicing, quotations, payments and financial reporting',
            ],
        ],
        [
            'name'        => 'HR',
            'alias'       => 'hr',
            'version'     => '1.0.0',
            'is_core'     => false,
            'is_enabled'  => true,
            'is_licensed' => true,
            'metadata'    => [
                'alias'       => 'hr',
                'order'       => 20,
                'is_core'     => false,
                'version'     => '1.0.0',
                'requires'    => ['Core'],
                'description' => 'Employee management, leave, and learning management',
            ],
        ],
        [
            'name'        => 'Bookings',
            'alias'       => 'bookings',
            'version'     => '1.0.0',
            'is_core'     => false,
            'is_enabled'  => true,
            'is_licensed' => true,
            'metadata'    => [
                'alias'       => 'bookings',
                'order'       => 30,
                'is_core'     => false,
                'version'     => '1.0.0',
                'requires'    => ['Core'],
                'description' => 'Resource and appointment booking management',
            ],
        ],
        [
            'name'        => 'Events',
            'alias'       => 'events',
            'version'     => '1.0.0',
            'is_core'     => false,
            'is_enabled'  => true,
            'is_licensed' => true,
            'metadata'    => [
                'alias'       => 'events',
                'order'       => 50,
                'is_core'     => false,
                'version'     => '1.0.0',
                'requires'    => ['Core', 'Financial'],
                'description' => 'Event ticketing — create events, sell tickets, PayFast payments',
            ],
        ],

        // Add new modules here as you build them.
        // [
        //     'name'        => 'Reporting',
        //     'alias'       => 'reporting',
        //     'version'     => '1.0.0',
        //     'is_core'     => false,
        //     'is_enabled'  => false,
        //     'is_licensed' => false,
        //     'metadata'    => [
        //         'alias'       => 'reporting',
        //         'order'       => 60,
        //         'is_core'     => false,
        //         'version'     => '1.0.0',
        //         'requires'    => ['Core'],
        //         'description' => 'Reporting & analytics module',
        //     ],
        // ],
    ];

    public function run(): void
    {
        foreach ($this->modules as $module) {
            // Keep metadata.version in sync with the top-level version so
            // you only ever need to edit one place when bumping a module.
            if (isset($module['metadata'])) {
                $module['metadata']['version'] = $module['version'];
            }

            $existing = DB::table('installed_modules')
                ->where('name', $module['name'])
                ->first();

            $payload = [
                'name'      => $module['name'],
                'alias'     => $module['alias'],
                'version'   => $module['version'],
                'is_core'   => $module['is_core'] ?? false,
                'metadata'  => isset($module['metadata']) ? json_encode($module['metadata']) : null,
                'updated_at' => now(),
            ];

            if ($existing) {
                // Only update fields that describe the module itself.
                // Enablement/licensing state is intentionally preserved
                // so re-running the seeder for a version bump never
                // silently flips a module's enabled/licensed status.
                DB::table('installed_modules')
                    ->where('id', $existing->id)
                    ->update($payload);

                if ($existing->version !== $module['version']) {
                    $this->command?->info(
                        "Updated [{$module['name']}] version: {$existing->version} -> {$module['version']}"
                    );
                }

                continue;
            }

            // New module — apply defaults for enablement/licensing timestamps.
            $isEnabled  = $module['is_enabled'] ?? false;
            $isLicensed = $module['is_licensed'] ?? false;

            DB::table('installed_modules')->insert(array_merge($payload, [
                'is_enabled'  => $isEnabled,
                'is_licensed' => $isLicensed,
                'enabled_at'  => $isEnabled ? now() : null,
                'licensed_at' => $isLicensed ? now() : null,
                'created_at'  => now(),
            ]));

            $this->command?->info("Installed module [{$module['name']}] v{$module['version']}");
        }
    }
}