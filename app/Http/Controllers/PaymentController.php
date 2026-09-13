<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Jobs\SendReceiptJob;
use App\PaymentGateways\GatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Services\InvoiceService;

class PaymentController extends Controller
{
    public function __construct(
        private GatewayManager $gatewayManager,
        private InvoiceService  $invoiceService,
    ) {}

    public function show(string $token)
    {
        $invoice = $this->findByToken($token);
        $gateway = $this->gatewayManager;
        $payment = Settings::group('payments');

        // Auto-set 48h EFT hold when no gateway configured
        if (! $gateway->isConfigured() && $invoice->status !== 'paid') {
            $invoice->setEftHold(48);
            $invoice->refresh();
        }

        return inertia('Payment/Show', [
            'invoice' => $this->formatInvoice($invoice),
            'gateway' => [
                'name'       => $gateway->gatewayName(),
                'configured' => $gateway->isConfigured(),
                'test_mode'  => $gateway->isTestMode(),
            ],
            'bank' => [
                'account_name'    => $payment->get('bank_account_name'),
                'bank_name'       => $payment->get('bank_name'),
                'account_number'  => $payment->get('bank_account_number'),
                'branch_code'     => $payment->get('bank_branch_code'),
                'reference'       => $payment->get('bank_reference_prefix') . $invoice->reference,
                'instructions'    => $payment->get('payment_instructions'),
            ],
            'app' => [
                'name'     => Settings::group('general')->get('app_name', config('app.name')),
                'logo_url' => Settings::group('general')->get('logo_url'),
            ],
            'eft' => [
                'hold_expires_at' => $invoice->eft_hold_expires_at?->toISOString(),
                'hold_active'     => $invoice->isEftHoldActive(),
                'pop_status'      => $invoice->pop_status ?? 'none',
                'pop_uploaded_at' => $invoice->pop_uploaded_at?->format('d M Y H:i'),
                'pop_file_name'   => $invoice->pop_original_name,
                'pop_notes'       => $invoice->pop_notes,
            ],
        ]);
    }

    public function initiate(Request $request, string $token)
    {
        $invoice = $this->findByToken($token);
        $gateway = $this->gatewayManager->active();

        if (! $gateway) {
            return back()->with('error', 'Online payments are not configured.');
        }

        // Backend determines what to charge — no client input needed
        $amount = $invoice->amountDueNow();

        if ($amount <= 0) {
            return redirect(route('pay.show', $token));
        }

        $returnUrl = route('pay.return', $token);
        $cancelUrl = route('pay.cancel', $token);
        $notifyUrl = route('webhooks.' . $this->gatewayManager->gatewayName());

        $redirectUrl = $gateway->initiatePayment(
            $invoice, $amount, $returnUrl, $cancelUrl, $notifyUrl
        );

        return redirect($redirectUrl);
    }

    public function handleReturn(Request $request, string $token)
    {
        $invoice = $this->findByToken($token);

        // ── Paystack redirect verification ────────────────────
        // Paystack appends ?reference=xxx to the callback URL.
        // We verify the transaction immediately here rather than
        // waiting for the webhook, giving the user instant feedback.
        $paystackRef = $request->query('reference') ?? $request->query('trxref');

        if ($paystackRef && $this->gatewayManager->gatewayName() === 'paystack') {
            $this->verifyPaystackReturn($invoice, $paystackRef);
            $invoice->refresh();
        }

        // ── Render appropriate page ───────────────────────────
        $appProps = [
            'name'     => Settings::group('general')->get('app_name', config('app.name')),
            'logo_url' => Settings::group('general')->get('logo_url'),
        ];

        if ($invoice->status === 'paid') {
            return inertia('Payment/Success', [
                'invoice' => $this->formatInvoice($invoice),
                'app'     => $appProps,
            ]);
        }

        if ($invoice->status === 'cancelled') {
            return inertia('Payment/Cancelled', [
                'invoice' => $this->formatInvoice($invoice),
                'app'     => $appProps,
            ]);
        }

        // PayFast: ITN hasn't arrived yet — show processing page.
        // Paystack: if we reach here, verification didn't succeed yet.
        return inertia('Payment/Processing', [
            'invoice' => $this->formatInvoice($invoice),
            'app'     => $appProps,
        ]);
    }

    /**
     * Verify a Paystack transaction immediately on redirect return.
     * This gives instant feedback vs waiting for the webhook.
     * The webhook will also fire — processSuccessfulPayment() in
     * WebhookController guards against double-processing.
     */
    private function verifyPaystackReturn(
        \Modules\Financial\app\Models\Invoice $invoice,
        string $reference,
    ): void {
        if ($invoice->status === 'paid') return; // already handled

        $gateway = $this->gatewayManager->active();

        if (! $gateway instanceof \App\PaymentGateways\PaystackGateway) return;

        $result = $gateway->verifyTransaction($reference);

        if (! $result) {
            \Illuminate\Support\Facades\Log::warning('Paystack return: verification failed', [
                'reference' => $reference,
                'invoice'   => $invoice->reference,
            ]);
            return;
        }

        // Verify the amount matches what we expect
        $expected = $invoice->amountDueNow();
        if (abs($result['amount'] - $expected) > 0.05) {
            \Illuminate\Support\Facades\Log::error('Paystack return: amount mismatch', [
                'expected'  => $expected,
                'received'  => $result['amount'],
                'reference' => $reference,
            ]);
            return;
        }

        $this->invoiceService->recordPayment($invoice, [
            'amount'    => $result['amount'],
            'method'    => 'paystack',
            'reference' => $reference,
            'notes'     => 'Online payment via Paystack',
            'paid_at'   => now()->format('Y-m-d H:i:s'),
        ]);

        $invoice->refresh();
        if ($invoice->status === 'paid') {
            \App\Jobs\SendReceiptJob::dispatch($invoice->id);
        }
    }

    public function handleCancel(string $token)
    {
        $invoice = Invoice::with(['customer', 'lines'])
            ->where('payment_token', $token)
            ->firstOrFail();

        return inertia('Payment/Cancelled', [
            'invoice' => $this->formatInvoice($invoice),
            'app'     => [
                'name'     => Settings::group('general')->get('app_name', config('app.name')),
                'logo_url' => Settings::group('general')->get('logo_url'),
            ],
        ]);
    }

     // ── EFT: set hold when customer views payment page with no gateway ────

    public function setEftHold(string $token)
    {
        $invoice = $this->findByToken($token);

        // Only set hold if no gateway configured and invoice not yet paid
        if (! $this->gatewayManager->isConfigured() && $invoice->status !== 'paid') {
            $invoice->setEftHold(48);
        }

        return response()->json(['expires_at' => $invoice->fresh()->eft_hold_expires_at]);
    }

    // ── EFT: customer uploads proof of payment ────────────────────────────

    public function uploadPop(Request $request, string $token)
    {
        $invoice = $this->findByToken($token);

        abort_if($invoice->status === 'paid', 422, 'Invoice is already paid.');
        abort_if(! $invoice->isEftHoldActive(), 410, 'Your reservation has expired.');

        $request->validate([
            'pop'       => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
            'pop_notes' => 'nullable|string|max:500',
        ]);

        // Delete old file if re-uploading
        if ($invoice->pop_path) {
            Storage::delete($invoice->pop_path);
        }

        $file = $request->file('pop');
        $path = $file->store('private/fin/pop', 'local');

        $invoice->update([
            'pop_path'          => $path,
            'pop_original_name' => $file->getClientOriginalName(),
            'pop_uploaded_at'   => now(),
            'pop_notes'         => $request->input('pop_notes'),
            'pop_status'        => 'pending',
        ]);
        // 1. Notify admin team
        try {
            \Illuminate\Support\Facades\Notification::route('mail', config('mail.from.admin_address'))
                ->notify(new \App\Notifications\PopUploadedNotification($invoice));
            \Illuminate\Support\Facades\Mail::to(config('mail.from.admin_address'))
                ->send(new \App\Mail\PopReceivedMailAdmin($invoice));
            Log::info('PoP admin notification sent for invoice ' . $invoice->reference);
        } catch (\Throwable $e) {
            Log::error('PoP admin notification failed: ' . $e->getMessage(), [
                'invoice' => $invoice->reference,
                'trace'   => $e->getTraceAsString(),
            ]);
        }

        // 2. Send customer confirmation
        try {
            if ($invoice->customer?->email) {
                \Illuminate\Support\Facades\Mail::to($invoice->customer->email)
                    ->send(new \App\Mail\PopReceivedMail($invoice));
                    Log::info('PoP customer confirmation sent to ' . $invoice->customer->email);
            }
        } catch (\Throwable $e) {
            Log::error('PoP customer confirmation failed: ' . $e->getMessage(), [
                'invoice' => $invoice->reference,
            ]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Proof of payment uploaded. We will verify and confirm your order within 1 business day.',
        ]);
    }

    // ── EFT: download PoP (admin) ─────────────────────────────────────────

    public function downloadPop(string $invoiceId)
    {
        $invoice = \Modules\Financial\app\Models\Invoice::findOrFail($invoiceId);

        abort_unless($invoice->pop_path && Storage::exists($invoice->pop_path), 404);

        return Storage::download($invoice->pop_path, $invoice->pop_original_name ?? 'proof-of-payment');
    }

    // ── EFT: admin approve PoP ────────────────────────────────────────────

    public function approvePop(Request $request, string $invoiceId)
    {
        $invoice = \Modules\Financial\app\Models\Invoice::findOrFail($invoiceId);

        abort_if($invoice->status === 'paid', 422, 'Already paid.');
        abort_if($invoice->pop_status !== 'pending', 422, 'No pending PoP to approve.');

        $invoice->update([
            'pop_status'      => 'approved',
            'pop_reviewed_by' => $request->user()->id,
            'pop_reviewed_at' => now(),
        ]);

        // Record payment via EFT
        $this->invoiceService->recordPayment($invoice, [
            'amount'    => $invoice->balance_due,
            'method'    => 'eft',
            'reference' => 'EFT-' . $invoice->reference,
            'notes'     => 'EFT payment confirmed via proof of payment',
            'paid_at'   => now()->format('Y-m-d H:i:s'),
        ]);

        $invoice->refresh();
        if ($invoice->status === 'paid') {
            \App\Jobs\SendReceiptJob::dispatch($invoice->id);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Payment approved']);
    }

    // ── EFT: admin reject PoP ─────────────────────────────────────────────

    public function rejectPop(Request $request, string $invoiceId)
    {
        $invoice = \Modules\Financial\app\Models\Invoice::findOrFail($invoiceId);

        $request->validate(['reason' => 'nullable|string|max:500']);

        $invoice->update([
            'pop_status'           => 'rejected',
            'pop_rejection_reason' => $request->input('reason'),
            'pop_reviewed_by'      => $request->user()->id,
            'pop_reviewed_at'      => now(),
        ]);

        // Notify customer
        try {
            \Illuminate\Support\Facades\Mail::to($invoice->customer->email)
                ->send(new \App\Mail\PopRejectedMail($invoice));
        } catch (\Throwable $e) {
            Log::warning('PoP rejection mail failed: ' . $e->getMessage());
        }

        return back()->with('toast', ['type' => 'warning', 'title' => 'PoP rejected — customer notified']);
    }

    private function findByToken(string $token): Invoice
    {
        $invoice = Invoice::with(['customer', 'lines'])
            ->where('payment_token', $token)
            ->first();

        abort_if(! $invoice, 404, 'Payment link not found.');
        abort_if(! $invoice->isPaymentTokenValid(), 410, 'This payment link has expired.');

        return $invoice;
    }

    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id'                 => $invoice->id,
            'reference'          => $invoice->reference,
            'status'             => $invoice->status,
            'customer_name'      => $invoice->customer->company_name,
            'customer_email'     => $invoice->customer->email,
            'lines'              => $invoice->lines->map(fn ($l) => [
                'description' => $l->description,
                'qty'         => $l->qty,
                'unit_price'  => $l->unit_price,
                'line_total'  => $l->line_total,
                'tax_rate'    => $l->tax_rate,
            ]),
            'subtotal'           => $invoice->subtotal,
            'tax_total'          => $invoice->tax_total,
            'total'              => $invoice->total,
            'paid_total'         => $invoice->paid_total,
            'balance_due'        => $invoice->balance_due,
            'deposit_required'   => $invoice->deposit_required,
            'deposit_percentage' => $invoice->deposit_percentage,
            'deposit_amount'     => $invoice->deposit_amount,
            'deposit_paid_at'    => $invoice->deposit_paid_at?->format('d M Y'),
            'issue_date'         => $invoice->issue_date?->format('d M Y'),
            'due_date'           => $invoice->due_date?->format('d M Y'),
            'currency'           => $invoice->currency,
            'token'              => $invoice->payment_token,
            'amount_due_now'     => $invoice->amountDueNow(),
            'payment_stage'      => $invoice->paymentStageLabel(),
        ];
    }
}
