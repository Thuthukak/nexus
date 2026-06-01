<?php

declare(strict_types=1);

namespace App\Listeners\Financial;

use App\Models\User;
use App\Notifications\InvoicePaidNotification;
use Modules\Financial\app\Events\InvoicePaid;
use Spatie\Permission\Models\Role;

class NotifyInvoicePaid
{
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;
        $invoice->load('createdBy');

        // Notify invoice creator
        if ($invoice->createdBy) {
            $invoice->createdBy->notify(new InvoicePaidNotification($invoice));
        }

        // Notify all admins
        User::role('Admin')
            ->where('id', '!=', $invoice->created_by)
            ->each(fn ($admin) =>
                $admin->notify(new InvoicePaidNotification($invoice))
            );
    }
}
