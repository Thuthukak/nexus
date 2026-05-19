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

        if (! $gateway || $gateway->getName() === 'none') {
            return response('Gateway not configured', 400);
        }

        $payload  = $request->all();
        $rawBody  = $request->getContent();
        $signature = $payload['signature'] ?? '';

        $result = $gateway->handleWebhook($payload, $rawBody, $signature);

        if (! $result) {
            Log::warning('PayFast webhook: invalid ITN received', $payload);
            return response('Invalid ITN', 400);
        }

        $this->processSuccessfulPayment(
            invoiceId:  $result['invoice_id'],
            amount:     $result['amount'],
            paymentRef: $result['payment_id'] ?? 'PayFast-' . time(),
            method:     'payfast',
        );

        return response('OK', 200);
    }

    public function paystack(Request $request)
    {
        $gateway   = $this->gatewayManager->active();
        $signature = $request->header('x-paystack-signature', '');
        $rawBody   = $request->getContent();
        $payload   = $request->all();

        $result = $gateway->handleWebhook($payload, $rawBody, $signature);

        if (! $result) {
            return response('Invalid webhook', 400);
        }

        $this->processSuccessfulPayment(
            invoiceId:  $result['invoice_id'],
            amount:     $result['amount'],
            paymentRef: $result['payment_id'] ?? 'Paystack-' . time(),
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

        if ($invoice->status === 'paid') {
            Log::info("Webhook: invoice {$invoiceId} already paid, skipping");
            return;
        }

        // Record the payment
        $this->invoiceService->recordPayment($invoice, [
            'amount'    => $amount,
            'method'    => $method,
            'reference' => $paymentRef,
            'notes'     => "Online payment via {$method}",
            'paid_at'   => now()->format('Y-m-d H:i:s'),
        ]);

        // Send receipt automatically
        SendReceiptJob::dispatch($invoice->id);

        Log::info("Webhook: payment of {$amount} recorded for invoice {$invoice->reference}");
    }
}
