<?php

declare(strict_types=1);

namespace Modules\Financial\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Financial';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('financial')
            ->name('financial.')
            ->group(module_path($this->moduleName, 'routes/web.php'));
    }
}
