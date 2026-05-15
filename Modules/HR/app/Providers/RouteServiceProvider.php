<?php

declare(strict_types=1);

namespace Modules\HR\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'HR';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('hr')
            ->name('hr.')
            ->group(module_path($this->moduleName, 'routes/web.php'));
    }
}
