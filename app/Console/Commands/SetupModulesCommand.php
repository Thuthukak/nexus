<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModuleRegistryService;
use Illuminate\Console\Command;

class SetupModulesCommand extends Command
{
    protected $signature   = 'nexus:setup-modules';
    protected $description = 'Seed installed_modules table for existing installations';

    public function handle(ModuleRegistryService $registry): int
    {
        $this->info('Seeding module registry for existing install...');

        $registry->seedForExistingInstall();

        $modules = $registry->getAllModules();

        $this->table(
            ['Module', 'Licensed', 'Enabled', 'Core'],
            collect($modules)->map(fn ($m) => [
                $m['name'],
                $m['is_licensed'] ? '✓' : '✗',
                $m['is_enabled']  ? '✓' : '✗',
                $m['is_core']     ? '✓' : '—',
            ])
        );

        $this->info('Done. Module registry is up to date.');

        return Command::SUCCESS;
    }
}
