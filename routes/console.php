<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Queue Processing — runs every minute via cron
|--------------------------------------------------------------------------
| Cron setup on the server:
| * * * * * cd /path/to/nexus && php artisan schedule:run >> /dev/null 2>&1
*/
Schedule::command('queue:work --stop-when-empty --timeout=55 --tries=3 --max-jobs=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| Recurring Invoices — daily at 06:00
|--------------------------------------------------------------------------
*/
Schedule::command('financial:process-recurring')
    ->dailyAt('06:00')
    ->withoutOverlapping();

Schedule::command('financial:process-overdue')
    ->dailyAt('06:05')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Daily maintenance
|--------------------------------------------------------------------------
*/
Schedule::command('queue:prune-failed --hours=168')->daily(); // keep 7 days
