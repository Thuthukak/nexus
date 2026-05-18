<?php

declare(strict_types=1);

namespace Modules\Financial\app\Console\Commands;

use Illuminate\Console\Command;
use Modules\Financial\app\Services\RecurringInvoiceService;

class ProcessRecurringInvoicesCommand extends Command
{
    protected $signature   = 'financial:process-recurring';
    protected $description = 'Generate invoices for all active recurring schedules due today';

    public function handle(RecurringInvoiceService $service): int
    {
        $this->info('Processing recurring invoices...');
        $count = $service->processAllDue();
        $this->info("Processed {$count} recurring invoice(s).");
        return Command::SUCCESS;
    }
}
