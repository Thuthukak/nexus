<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Financial\app\Models\Invoice;

class ExpireEftHoldsCommand extends Command
{
    protected $signature   = 'fin:expire-eft-holds';
    protected $description = 'Cancel invoices whose EFT hold has expired without payment or PoP';

    public function handle(): void
    {
        $expired = Invoice::whereNotNull('eft_hold_expires_at')
            ->where('eft_hold_expires_at', '<', now())
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereIn('pop_status', ['none', 'rejected']) // don't cancel pending PoP review
            ->get();

        foreach ($expired as $invoice) {
            $invoice->update(['status' => 'cancelled']);
            $this->line("Cancelled: {$invoice->reference}");
        }

        $this->info("Expired {$expired->count()} EFT hold(s).");
    }
}
