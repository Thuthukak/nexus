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
use App\Services\ActivityLogService;
use App\Services\ModuleRegistryService;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Modules\Financial\app\Events\InvoiceApproved;
use Modules\Financial\app\Events\InvoicePaid;
use Modules\Financial\app\Events\InvoiceOverdue;
use Modules\HR\app\Events\LeaveApplicationSubmitted;
use Modules\HR\app\Events\LeaveApplicationApproved;
use Modules\HR\app\Events\LeaveApplicationRejected;
use Modules\Bookings\app\Events\BookingConfirmed;
use Modules\Bookings\app\Events\BookingCancelled;
use App\Listeners\Financial\NotifyInvoiceApproved;
use App\Listeners\Financial\NotifyInvoicePaid;
use App\Listeners\Financial\NotifyInvoiceOverdue;
use App\Listeners\HR\NotifyLeaveSubmitted;
use App\Listeners\HR\NotifyLeaveDecision;
use App\Listeners\Bookings\NotifyBookingStatusChange;
use App\Listeners\UpdateLastLogin;

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

        $this->app->singleton(ActivityLogService::class);
        
        $this->app->singleton(ModuleRegistryService::class);
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

        $this->commands([\App\Console\Commands\SetupModulesCommand::class,]);
        $this->commands([\App\Console\Commands\SetupModulesCommand::class,]);
        
        Event::listen(Login::class, UpdateLastLogin::class);

        // Financial notifications
        Event::listen(InvoiceApproved::class, NotifyInvoiceApproved::class);
        Event::listen(InvoicePaid::class,     NotifyInvoicePaid::class);
        Event::listen(InvoiceOverdue::class,  NotifyInvoiceOverdue::class);

        // HR notifications
        Event::listen(LeaveApplicationSubmitted::class, NotifyLeaveSubmitted::class);
        Event::listen(LeaveApplicationApproved::class,  [NotifyLeaveDecision::class, 'handleApproved']);
        Event::listen(LeaveApplicationRejected::class,  [NotifyLeaveDecision::class, 'handleRejected']);

        // Bookings notifications
        Event::listen(BookingConfirmed::class, [NotifyBookingStatusChange::class, 'handleConfirmed']);
        Event::listen(BookingCancelled::class, [NotifyBookingStatusChange::class, 'handleCancelled']);
    }
}
