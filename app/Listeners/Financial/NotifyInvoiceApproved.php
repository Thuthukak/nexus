<?php

declare(strict_types=1);

namespace App\Listeners\Financial;

use App\Notifications\InvoiceApprovedNotification;
use Modules\Financial\app\Events\InvoiceApproved;

class NotifyInvoiceApproved
{
    public function handle(InvoiceApproved $event): void
    {
        $invoice = $event->invoice;
        $invoice->load('createdBy');

        if ($invoice->createdBy) {
            $invoice->createdBy->notify(new InvoiceApprovedNotification($invoice));
        }
    }
}
