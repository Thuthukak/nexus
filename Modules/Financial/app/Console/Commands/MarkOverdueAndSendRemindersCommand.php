<?php

declare(strict_types=1);

namespace Modules\Financial\app\Console\Commands;

use App\Facades\Settings;
use App\Mail\InvoiceReminderMail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Financial\app\Models\Invoice;

class MarkOverdueAndSendRemindersCommand extends Command
{
    protected $signature   = 'financial:process-overdue';
    protected $description = 'Mark overdue invoices and send payment reminders';

    public function handle(): int
    {
        $paymentSettings  = Settings::group('payments');
        $graceDays        = (int) ($paymentSettings->get('grace_days', 7));
        $intervalDays     = (int) ($paymentSettings->get('reminder_interval_days', 7));

        $this->info("Grace period: {$graceDays} days | Reminder interval: {$intervalDays} days");

        // Step 1 — Mark sent invoices as overdue if past due date
        $markedOverdue = Invoice::whereIn('status', ['sent', 'approved', 'deposit_paid', 'part_paid'])
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $this->info("Marked {$markedOverdue} invoice(s) as overdue.");

        // Step 2 — Find overdue invoices that need a reminder
        $overdueInvoices = Invoice::with(['customer', 'lines'])
            ->where('status', 'overdue')
            ->where('balance_due', '>', 0)
            ->whereNotNull('customer_id')
            ->get()
            ->filter(fn ($inv) => $this->shouldSendReminder($inv, $intervalDays));

        $sent = 0;

        foreach ($overdueInvoices as $invoice) {
            if (! $invoice->customer?->email) {
                $this->warn("Invoice {$invoice->reference}: customer has no email, skipping.");
                continue;
            }

            try {
                // Refresh payment token if expired or about to expire
                if (! $invoice->isPaymentTokenValid()) {
                    $invoice->generatePaymentToken($graceDays);
                    $invoice->refresh();
                }

                $newCount = ($invoice->reminder_count ?? 0) + 1;

                Mail::to($invoice->customer->email)
                    ->queue(new InvoiceReminderMail($invoice, $newCount));

                $invoice->update([
                    'reminder_count'       => $newCount,
                    'last_reminder_sent_at'=> now(),
                ]);

                $this->info("Reminder #{$newCount} queued for {$invoice->reference} → {$invoice->customer->email}");
                $sent++;

            } catch (\Throwable $e) {
                Log::error("Reminder failed for invoice {$invoice->reference}: " . $e->getMessage());
                $this->error("Failed for {$invoice->reference}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$sent} reminder(s).");

        return Command::SUCCESS;
    }

    private function shouldSendReminder(Invoice $invoice, int $intervalDays): bool
    {
        // Never sent a reminder yet — send one now
        if (! $invoice->last_reminder_sent_at) {
            return true;
        }

        // Check if enough days have passed since last reminder
        return $invoice->last_reminder_sent_at
            ->addDays($intervalDays)
            ->isPast();
    }
}
