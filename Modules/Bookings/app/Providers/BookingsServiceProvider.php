<?php

declare(strict_types=1);

namespace Modules\Bookings\app\Providers;

use Illuminate\Support\ServiceProvider;

class BookingsServiceProvider extends ServiceProvider
{
    protected string $moduleName      = 'Bookings';
    protected string $moduleNameLower = 'bookings';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
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
