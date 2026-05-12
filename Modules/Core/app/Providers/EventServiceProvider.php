<?php

declare(strict_types=1);

namespace Modules\Core\app\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Maps Events to Listeners for the Core module.
     * Cross-module events are registered here when Core
     * needs to react to other modules' events.
     */
    protected $listen = [];

    public function boot(): void
    {
        parent::boot();
    }
}
