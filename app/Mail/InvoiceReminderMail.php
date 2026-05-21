<?php

declare(strict_types=1);

namespace App\Mail;

use App\Facades\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Financial\app\Models\Invoice;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly int     $reminderCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Reminder — Invoice ' . $this->invoice->reference . ' is Overdue',
        );
    }

    public function content(): Content
    {
        $paymentSettings = Settings::group('payments');
        $bankReference   = ($paymentSettings->get('bank_reference_prefix') ?? 'INV-')
            . $this->invoice->reference;

        return new Content(
            view: 'financial::emails.invoice-reminder',
            with: [
                'invoice'       => $this->invoice,
                'reminderCount' => $this->reminderCount,
                'appName'       => config('app.name'),
                'currency'      => config('financial.currency', 'ZAR'),
                'paymentUrl'    => $this->invoice->isPaymentTokenValid()
                    ? $this->invoice->payment_url
                    : null,
                'bankDetails'   => [
                    'account_name'   => $paymentSettings->get('bank_account_name'),
                    'bank_name'      => $paymentSettings->get('bank_name'),
                    'account_number' => $paymentSettings->get('bank_account_number'),
                    'branch_code'    => $paymentSettings->get('bank_branch_code'),
                    'reference'      => $bankReference,
                    'instructions'   => $paymentSettings->get('payment_instructions'),
                ],
            ],
        );
    }
}
