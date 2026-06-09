<?php

declare(strict_types=1);

namespace Modules\LMS\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'LMS';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        Route::middleware(['web', 'auth', 'module:LMS'])
            ->prefix('lms')
            ->name('lms.')
            ->group(module_path($this->moduleName, 'routes/web.php'));
    }
}
