<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\LicenceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LicenceService::class, function () {
            return new LicenceService();
        });
    }

    public function boot(): void
    {
        //
    }
}
