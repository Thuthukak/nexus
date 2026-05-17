<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessQueueCommand extends Command
{
    protected $signature   = 'queue:process-batch';
    protected $description = 'Process a batch of queued jobs (used by scheduler)';

    public function handle(): void
    {
        // Run queue worker for 55 seconds max so it fits within a 1-minute cron window
        $this->call('queue:work', [
            '--stop-when-empty' => true,
            '--timeout'         => 55,
            '--tries'           => 3,
            '--max-jobs'        => 50,
        ]);
    }
}
