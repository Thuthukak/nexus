<?php

declare(strict_types=1);

namespace Modules\Financial\app\Providers;

use Illuminate\Support\ServiceProvider;

class FinancialServiceProvider extends ServiceProvider
{
    protected string $moduleName      = 'Financial';
    protected string $moduleNameLower = 'financial';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->commands([
            \Modules\Financial\app\Console\Commands\ProcessRecurringInvoicesCommand::class,
        ]);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(
            module_path($this->moduleName, 'resources/views'),
            $this->moduleNameLower
        );
    }
}
