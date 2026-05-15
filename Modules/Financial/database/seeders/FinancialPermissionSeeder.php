<?php

declare(strict_types=1);

namespace Modules\Financial\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FinancialPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'financial.invoices.view',
            'financial.invoices.create',
            'financial.invoices.edit',
            'financial.invoices.delete',
            'financial.invoices.approve',
            'financial.invoices.export',
            'financial.quotations.view',
            'financial.quotations.create',
            'financial.quotations.edit',
            'financial.quotations.approve',
            'financial.customers.manage',
            'financial.reports.view',
            'financial.portal.invoices.view',
            'financial.portal.invoices.pay',
            'financial.portal.quotations.view',
            'financial.portal.quotations.accept',
            'financial.portal.statements.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::findByName('Admin', 'web');
        $admin->givePermissionTo(array_filter(
            $permissions,
            fn ($p) => ! str_contains($p, '.portal.')
        ));

        $manager = Role::findByName('Manager', 'web');
        $manager->givePermissionTo([
            'financial.invoices.view',
            'financial.invoices.create',
            'financial.quotations.view',
            'financial.quotations.create',
            'financial.reports.view',
        ]);

        $staff = Role::findByName('Staff', 'web');
        $staff->givePermissionTo([
            'financial.invoices.view',
            'financial.invoices.create',
            'financial.quotations.view',
            'financial.quotations.create',
        ]);

        $this->command->info('Financial permissions seeded.');
    }
}
