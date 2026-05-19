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
        $response = Http::withToken($this->secretKey)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email'      => $invoice->customer->email,
                'amount'     => (int) round($amount * 100), // Paystack uses kobo/cents
                'currency'   => 'ZAR',
                'reference'  => 'INV-' . $invoice->id . '-' . time(),
                'callback_url' => $returnUrl,
                'metadata'   => [
                    'invoice_id'  => $invoice->id,
                    'invoice_ref' => $invoice->reference,
                    'cancel_action' => $cancelUrl,
                ],
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            Log::error('Paystack initiation failed', $response->json());
            throw new \RuntimeException('Failed to initiate Paystack payment');
        }

        return $response->json('data.authorization_url');
    }

    public function verifyPayment(string $reference, float $expectedAmount): bool
    {
        $response = Http::withToken($this->secretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (! $response->successful()) return false;

        $data   = $response->json('data');
        $status = $data['status'] ?? '';
        $amount = ($data['amount'] ?? 0) / 100;

        return $status === 'success' && abs($amount - $expectedAmount) < 0.01;
    }

    public function handleWebhook(array $payload, string $rawBody, string $signature): ?array
    {
        // Verify webhook signature
        $computed = hash_hmac('sha512', $rawBody, $this->secretKey);
        if (! hash_equals($computed, $signature)) {
            Log::warning('Paystack webhook signature mismatch');
            return null;
        }

        if (($payload['event'] ?? '') !== 'charge.success') {
            return null;
        }

        $data = $payload['data'] ?? [];

        return [
            'invoice_id' => $data['metadata']['invoice_id'] ?? null,
            'reference'  => $data['metadata']['invoice_ref'] ?? null,
            'amount'     => ($data['amount'] ?? 0) / 100,
            'payment_id' => $data['reference'] ?? null,
        ];
    }
}
