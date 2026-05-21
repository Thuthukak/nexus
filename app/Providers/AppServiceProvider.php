<?php

declare(strict_types=1);

namespace App\Providers;

use App\PaymentGateways\GatewayManager;
use App\PaymentGateways\PayfastGateway;
use App\PaymentGateways\PaystackGateway;
use App\Services\LicenceService;
use App\Settings\SettingsService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LicenceService::class, function () {
            return new LicenceService();
        });

        $this->app->bind(SettingsService::class, function () {
            return new SettingsService();
        });

        $this->app->singleton(GatewayManager::class);
        $this->app->bind(PayfastGateway::class);
        $this->app->bind(PaystackGateway::class);
    }

    public function boot(): void
    {
        if (app()->environment('local')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Settings', \App\Facades\Settings::class);
        });
    }
}
