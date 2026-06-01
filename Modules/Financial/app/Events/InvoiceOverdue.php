<?php

declare(strict_types=1);

namespace Modules\Financial\app\Events;

use Modules\Financial\app\Models\Invoice;

class InvoiceOverdue
{
    public function __construct(
        public readonly Invoice $invoice
    ) {}
}
