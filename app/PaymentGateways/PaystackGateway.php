<?php

declare(strict_types=1);

namespace App\PaymentGateways;

use App\Facades\Settings;
use App\PaymentGateways\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Financial\app\Models\Invoice;

class PaystackGateway implements PaymentGatewayInterface
{
    private string $publicKey;
    private string $secretKey;
    private bool   $testMode;

    public function __construct()
    {
        $settings        = Settings::group('payments');
        $this->testMode  = (bool) $settings->get('test_mode', true);
        $this->publicKey = $settings->get('paystack_public_key', '') ?? '';
        $this->secretKey = $settings->get('paystack_secret_key', '') ?? '';
    }

    public function getName(): string
    {
        return 'Paystack' . ($this->testMode ? ' (Test)' : '');
    }

    public function initiatePayment(
        Invoice $invoice,
        float   $amount,
        string  $returnUrl,
        string  $cancelUrl,
        string  $notifyUrl,
    ): string {
        // Paystack amounts are in the smallest currency unit (cents for ZAR)
        $amountInCents = (int) round($amount * 100);

        // Reference must be unique per transaction
        $reference = 'INV-' . $invoice->id . '-' . time();

        $response = Http::withToken($this->secretKey)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email'        => $invoice->customer->email,
                'amount'       => $amountInCents,
                'currency'     => 'ZAR',
                'reference'    => $reference,
                'callback_url' => $returnUrl,
                'metadata'     => [
                    'invoice_id'    => $invoice->id,
                    'invoice_ref'   => $invoice->reference,
                    'cancel_action' => $cancelUrl,
                    // Store reference so we can verify in the webhook
                    'tx_reference'  => $reference,
                ],
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            Log::error('Paystack initiation failed', [
                'invoice'  => $invoice->reference,
                'response' => $response->json(),
            ]);
            throw new \RuntimeException('Failed to initiate Paystack payment: ' . $response->json('message', 'Unknown error'));
        }

        return $response->json('data.authorization_url');
    }

    public function verifyPayment(string $reference, float $expectedAmount): bool
    {
        $response = Http::withToken($this->secretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (! $response->successful()) {
            Log::warning('Paystack verification failed', ['reference' => $reference]);
            return false;
        }

        $data   = $response->json('data');
        $status = $data['status'] ?? '';
        // Paystack returns amount in cents — convert back for comparison
        $paidAmount = ($data['amount'] ?? 0) / 100;

        // Allow 1 cent tolerance for floating point rounding
        return $status === 'success' && abs($paidAmount - $expectedAmount) < 0.01;
    }

    public function handleWebhook(array $payload, string $rawBody, string $signature): ?array
    {
        // Paystack signs the raw request body with HMAC-SHA512
        $computed = hash_hmac('sha512', $rawBody, $this->secretKey);

        if (! hash_equals($computed, $signature)) {
            Log::warning('Paystack webhook signature mismatch');
            return null;
        }

        // We only act on successful charges
        if (($payload['event'] ?? '') !== 'charge.success') {
            return null;
        }

        $data = $payload['data'] ?? [];

        // Amount comes back in cents
        $amount = ($data['amount'] ?? 0) / 100;

        return [
            'invoice_id' => $data['metadata']['invoice_id']  ?? null,
            'reference'  => $data['metadata']['invoice_ref'] ?? null,
            'amount'     => $amount,
            'payment_id' => $data['reference']               ?? null,
        ];
    }
}