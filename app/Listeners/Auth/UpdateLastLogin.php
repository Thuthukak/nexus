<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Login;

class UpdateLastLogin
{
    public function handle(Login $event): void
    {
        $event->user->update(['last_login_at' => now()]);
    }
}
