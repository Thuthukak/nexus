<?php

namespace Modules\Events\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Events';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        Route::middleware(['web', 'auth', 'module:Events'])
            ->prefix('events')
            ->name('events.')
            ->group(module_path($this->moduleName, 'routes/web.php'));
    }
}
