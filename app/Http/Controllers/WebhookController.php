<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SendReceiptJob;
use App\PaymentGateways\GatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Services\InvoiceService;

class WebhookController extends Controller
{
    public function __construct(
        private GatewayManager $gatewayManager,
        private InvoiceService  $invoiceService,
    ) {}

    public function payfast(Request $request)
    {
        $gateway = $this->gatewayManager->active();

        if (! $gateway) {
            Log::warning('PayFast ITN received but no gateway is configured');
            // Still return 200 — returning 400 causes PayFast to retry indefinitely
            return response('OK', 200);
        }

        $payload   = $request->all();
        $rawBody   = $request->getContent();
        $signature = $payload['signature'] ?? '';

        $result = $gateway->handleWebhook($payload, $rawBody, $signature);

        if (! $result) {
            Log::warning('PayFast webhook: invalid or incomplete ITN', [
                'payment_status' => $payload['payment_status'] ?? 'unknown',
                'm_payment_id'   => $payload['m_payment_id']   ?? 'unknown',
            ]);
            // Return 200 for non-COMPLETE statuses (FAILED, CANCELLED) — these are
            // valid ITNs, just not ones we act on. 400 triggers retries.
            return response('OK', 200);
        }

        $this->processSuccessfulPayment(
            invoiceId:  $result['invoice_id'],
            amount:     $result['amount'],
            paymentRef: $result['payment_id'] ?? 'payfast-' . time(),
            method:     'payfast',
        );

        return response('OK', 200);
    }

    public function paystack(Request $request)
    {
        $gateway = $this->gatewayManager->active();

        if (! $gateway) {
            Log::warning('Paystack webhook received but no gateway is configured');
            return response('OK', 200);
        }

        $signature = $request->header('x-paystack-signature', '');
        $rawBody   = $request->getContent();
        $payload   = $request->all();

        $result = $gateway->handleWebhook($payload, $rawBody, $signature);

        if (! $result) {
            // Non-charge.success events (subscription, refund, etc.) return null — that's fine
            return response('OK', 200);
        }

        $this->processSuccessfulPayment(
            invoiceId:  $result['invoice_id'],
            amount:     $result['amount'],
            paymentRef: $result['payment_id'] ?? 'paystack-' . time(),
            method:     'paystack',
        );

        return response('OK', 200);
    }

    private function processSuccessfulPayment(
        ?string $invoiceId,
        float   $amount,
        string  $paymentRef,
        string  $method,
    ): void {
        if (! $invoiceId) {
            Log::error('Webhook: no invoice_id in payload');
            return;
        }

        $invoice = Invoice::find($invoiceId);

        if (! $invoice) {
            Log::error("Webhook: invoice {$invoiceId} not found");
            return;
        }

        // Guard against duplicate ITNs — PayFast can send the same ITN multiple times
        if ($invoice->status === 'paid') {
            Log::info("Webhook: invoice {$invoice->reference} already paid, skipping duplicate ITN");
            return;
        }

        // Guard against amount mismatch — never trust the gateway amount blindly
        $expected = $invoice->amountDueNow();
        if (abs($amount - $expected) > 0.05) {
            Log::error("Webhook: amount mismatch for {$invoice->reference}", [
                'expected' => $expected,
                'received' => $amount,
                'method'   => $method,
            ]);
            return;
        }

        $this->invoiceService->recordPayment($invoice, [
            'amount'    => $amount,
            'method'    => $method,
            'reference' => $paymentRef,
            'notes'     => "Online payment via {$method}",
            'paid_at'   => now()->format('Y-m-d H:i:s'),
        ]);

        // Only send receipt once the invoice is fully paid
        $invoice->refresh();
        if ($invoice->status === 'paid') {
            SendReceiptJob::dispatch($invoice->id);
            Log::info("Webhook: receipt queued for {$invoice->reference}");
        }

        Log::info("Webhook: R{$amount} recorded for invoice {$invoice->reference} via {$method}");
    }
}