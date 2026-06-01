<?php

declare(strict_types=1);

namespace App\Listeners\Financial;

use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use Modules\Financial\app\Events\InvoiceOverdue;

class NotifyInvoiceOverdue
{
    public function handle(InvoiceOverdue $event): void
    {
        $invoice = $event->invoice;

        // Notify admins about overdue invoices
        User::role('Admin')
            ->each(fn ($admin) =>
                $admin->notify(new InvoiceOverdueNotification($invoice))
            );
    }
}
