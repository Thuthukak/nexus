<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleRegistryService
{
    private const CACHE_KEY = 'installed_modules';
    private const CACHE_TTL = 3600;

    // All known modules in the platform
    private const KNOWN_MODULES = [
        'Core' => [
            'alias'       => 'core',
            'version'     => '2.0.0',
            'is_core'     => true,
            'description' => 'Core platform — auth, users, roles, settings',
            'requires'    => [],
            'order'       => 0,
        ],
        'Financial' => [
            'alias'       => 'financial',
            'version'     => '2.0.0',
            'is_core'     => false,
            'description' => 'Invoicing, quotations, payments and financial reporting',
            'requires'    => ['Core'],
            'order'       => 10,
        ],
        'HR' => [
            'alias'       => 'hr',
            'version'     => '1.0.0',
            'is_core'     => false,
            'description' => 'Employee management, leave, and learning management',
            'requires'    => ['Core'],
            'order'       => 20,
        ],
        'LMS' => [
            'alias'       => 'lms',
            'version'     => '1.0.0',
            'is_core'     => false,
            'description' => 'Learning Management System — courses, cohorts, quizzes and certificates',
            'requires'    => ['Core'],
            'order'       => 40,
        ],
        'Bookings' => [
            'alias'       => 'bookings',
            'version'     => '1.0.0',
            'is_core'     => false,
            'description' => 'Resource and appointment booking management',
            'requires'    => ['Core'],
            'order'       => 30,
        ],
        'Events' => [
            'alias'       => 'events',
            'version'     => '1.0.0',
            'is_core'     => false,
            'description' => 'Event ticketing — create events, sell tickets, PayFast payments',
            'requires'    => ['Core', 'Financial'],
            'order'       => 50,
        ],
    ];

    /**
     * Get all enabled module names for the current installation.
     * Used by HandleInertiaRequests to share with frontend.
     */
    public function getEnabledModules(): array
    {
        if (! $this->tableExists()) {
            return app()->environment('local')
                ? array_keys(self::KNOWN_MODULES)
                : ['Core'];
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return DB::table('installed_modules')
                ->where('is_enabled', true)
                ->pluck('name')
                ->toArray();
        });
    }

    /**
     * Get all modules with full status — used by Module Manager and Wizard.
     */
    public function getAllModules(): array
    {
        $licensed = app(LicenceService::class)->getAllowedModules();
        $stored   = $this->getStoredModules();

        return collect(self::KNOWN_MODULES)
            ->map(function (array $config, string $name) use ($licensed, $stored) {
                $record = $stored[$name] ?? null;
                return [
                    'name'        => $name,
                    'alias'       => $config['alias'],
                    'version'     => $config['version'],
                    'is_core'     => $config['is_core'],
                    'description' => $config['description'],
                    'requires'    => $config['requires'],
                    'order'       => $config['order'],
                    'is_licensed' => $config['is_core'] || in_array($name, $licensed, true),
                    'is_enabled'  => $record?->is_enabled ?? $config['is_core'],
                    'enabled_at'  => $record?->enabled_at,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Enable a module — checks licence first.
     */
    public function enable(string $moduleName): bool
    {
        $licence = app(LicenceService::class);

        if (! $licence->isModuleAllowed($moduleName) && ! app()->environment('local')) {
            return false;
        }

        DB::table('installed_modules')->upsert(
            [
                'name'        => $moduleName,
                'alias'       => strtolower($moduleName),
                'version'     => self::KNOWN_MODULES[$moduleName]['version'] ?? '1.0.0',
                'is_enabled'  => true,
                'is_licensed' => true,
                'is_core'     => self::KNOWN_MODULES[$moduleName]['is_core'] ?? false,
                'metadata'    => json_encode(self::KNOWN_MODULES[$moduleName] ?? []),
                'enabled_at'  => now(),
                'licensed_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            ['name'],
            ['is_enabled', 'is_licensed', 'enabled_at', 'updated_at']
        );

        $this->clearCache();
        activity('module')->causedBy(auth()->user())->log("Module {$moduleName} enabled");
        return true;
    }

    /**
     * Disable a module — Core cannot be disabled.
     */
    public function disable(string $moduleName): bool
    {
        if ($moduleName === 'Core') return false;

        DB::table('installed_modules')
            ->where('name', $moduleName)
            ->update(['is_enabled' => false, 'updated_at' => now()]);

        $this->clearCache();
        activity('module')->causedBy(auth()->user())->log("Module {$moduleName} disabled");
        return true;
    }

    /**
     * Seed all known modules into the database for existing installs.
     */
    public function seedForExistingInstall(): void
    {
        $licensed = app(LicenceService::class)->getAllowedModules();

        foreach (self::KNOWN_MODULES as $name => $config) {
            $isLicensed = $config['is_core'] || in_array($name, $licensed, true);

            DB::table('installed_modules')->upsert(
                [
                    'name'        => $name,
                    'alias'       => $config['alias'],
                    'version'     => $config['version'],
                    'is_enabled'  => $isLicensed, // enable all licensed on seed
                    'is_licensed' => $isLicensed,
                    'is_core'     => $config['is_core'],
                    'metadata'    => json_encode($config),
                    'enabled_at'  => $isLicensed ? now() : null,
                    'licensed_at' => $isLicensed ? now() : null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                ['name'],
                ['is_enabled', 'is_licensed', 'enabled_at', 'licensed_at', 'updated_at']
            );
        }

        $this->clearCache();
    }

    public function isEnabled(string $moduleName): bool
    {
        if (app()->environment('local')) return true;
        return in_array($moduleName, $this->getEnabledModules(), true);
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function getStoredModules(): array
    {
        if (! $this->tableExists()) return [];

        return DB::table('installed_modules')
            ->get()
            ->keyBy('name')
            ->toArray();
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('installed_modules');
        } catch (\Throwable) {
            return false;
        }
    }
}
