<?php

declare(strict_types=1);

namespace Modules\LMS\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'LMS';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        // Admin / teacher LMS routes
        Route::middleware(['web', 'auth', 'module:LMS'])
            ->prefix('lms')
            ->name('lms.')
            ->group(module_path($this->moduleName, 'routes/web.php'));

        // Student portal routes — separate prefix, same web+auth middleware
        Route::middleware(['web', 'auth'])
            ->prefix('student')
            ->name('student.')
            ->group(module_path($this->moduleName, 'routes/student.php'));
    }
}
