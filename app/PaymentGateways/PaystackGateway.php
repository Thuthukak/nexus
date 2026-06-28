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
    private const API_BASE = 'https://api.paystack.co';

    private string $secretKey;
    private string $publicKey;
    private bool   $testMode;

    public function __construct()
    {
        $settings        = Settings::group('payments');
        $this->testMode  = (bool) $settings->get('test_mode', true);
        $this->secretKey = $settings->get('paystack_secret_key', '');
        $this->publicKey = $settings->get('paystack_public_key', '');
    }

    public function getName(): string
    {
        return 'Paystack' . ($this->testMode ? ' (Test)' : '');
    }

    // ── Initiate payment ─────────────────────────────────────

    public function initiatePayment(
        Invoice $invoice,
        float   $amount,
        string  $returnUrl,
        string  $cancelUrl,
        string  $notifyUrl,
    ): string {
        // Paystack requires amount in kobo/cents (smallest currency unit)
        // ZAR: R100.00 → 10000 kobo
        $amountInKobo = (int) round($amount * 100);

        $reference = $this->buildReference($invoice);

        $payload = [
            'email'        => $invoice->customer->email,
            'amount'       => $amountInKobo,
            'currency'     => $invoice->currency ?? 'ZAR',
            'reference'    => $reference,
            'callback_url' => $returnUrl,
            'metadata'     => [
                'invoice_id'        => $invoice->id,
                'invoice_reference' => $invoice->reference,
                'custom_fields'     => [
                    [
                        'display_name' => 'Invoice',
                        'variable_name'=> 'invoice_reference',
                        'value'        => $invoice->reference,
                    ],
                    [
                        'display_name' => 'Customer',
                        'variable_name'=> 'customer_name',
                        'value'        => $invoice->customer->company_name
                            ?? $invoice->customer->contact_name
                            ?? $invoice->customer->email,
                    ],
                ],
            ],
        ];

        $response = Http::withToken($this->secretKey)
            ->post(self::API_BASE . '/transaction/initialize', $payload);

        if (! $response->successful() || ! $response->json('status')) {
            $message = $response->json('message') ?? 'Unknown error';
            Log::error('Paystack: failed to initialize transaction', [
                'invoice'  => $invoice->reference,
                'response' => $response->json(),
            ]);
            throw new \RuntimeException("Paystack initialization failed: {$message}");
        }

        $authorizationUrl = $response->json('data.authorization_url');

        // Store the reference on the invoice so we can verify after redirect
        $invoice->update(['payment_token' => $invoice->payment_token]); // token stays the same
        // We store the paystack reference in cache for the return handler to verify
        cache()->put(
            "paystack_ref:{$invoice->payment_token}",
            $reference,
            now()->addHours(2)
        );

        Log::info('Paystack: transaction initialized', [
            'invoice'   => $invoice->reference,
            'reference' => $reference,
            'amount'    => $amount,
        ]);

        return $authorizationUrl;
    }

    // ── Verify after redirect callback ───────────────────────

    /**
     * Called from PaymentController::handleReturn() after Paystack
     * redirects back with ?reference=xxx in the URL.
     * Returns the verified amount in ZAR (not kobo).
     */
    public function verifyTransaction(string $reference): ?array
    {
        $response = Http::withToken($this->secretKey)
            ->get(self::API_BASE . '/transaction/verify/' . urlencode($reference));

        if (! $response->successful()) {
            Log::error('Paystack: verification request failed', [
                'reference' => $reference,
                'status'    => $response->status(),
            ]);
            return null;
        }

        $data = $response->json('data');

        if (! $data || ($data['status'] ?? '') !== 'success') {
            Log::warning('Paystack: transaction not successful', [
                'reference' => $reference,
                'status'    => $data['status'] ?? 'unknown',
            ]);
            return null;
        }

        return [
            'reference'  => $data['reference'],
            'amount'     => (float) $data['amount'] / 100, // kobo → ZAR
            'invoice_id' => $data['metadata']['invoice_id'] ?? null,
            'currency'   => $data['currency'] ?? 'ZAR',
            'channel'    => $data['channel'] ?? 'unknown',
            'paid_at'    => $data['paid_at'] ?? null,
        ];
    }

    // ── Webhook handler (charge.success events) ───────────────

    public function handleWebhook(array $payload, string $rawBody, string $signature): ?array
    {
        // Verify HMAC SHA512 signature
        if (! $this->verifySignature($rawBody, $signature)) {
            Log::warning('Paystack webhook: invalid signature');
            return null;
        }

        $event = $payload['event'] ?? '';

        // We only act on charge.success — return null for all other events
        // (subscription, refund, transfer, etc.) so WebhookController returns 200
        if ($event !== 'charge.success') {
            Log::info("Paystack webhook: ignoring event '{$event}'");
            return null;
        }

        $data   = $payload['data']   ?? [];
        $status = $data['status']    ?? '';

        if ($status !== 'success') {
            Log::info('Paystack webhook: charge event status is not success', [
                'status' => $status,
            ]);
            return null;
        }

        // Amount comes in kobo — convert to ZAR
        $amountZar  = (float) ($data['amount'] ?? 0) / 100;
        $reference  = $data['reference'] ?? null;
        $metadata   = $data['metadata']  ?? [];
        $invoiceId  = $metadata['invoice_id'] ?? null;

        if (! $invoiceId || ! $reference) {
            Log::error('Paystack webhook: missing invoice_id or reference in metadata', [
                'metadata'  => $metadata,
                'reference' => $reference,
            ]);
            return null;
        }

        Log::info('Paystack webhook: charge.success received', [
            'reference'  => $reference,
            'invoice_id' => $invoiceId,
            'amount'     => $amountZar,
        ]);

        return [
            'invoice_id' => $invoiceId,
            'reference'  => $reference,
            'amount'     => $amountZar,
            'payment_id' => $reference,
        ];
    }

    // ── Legacy interface method ───────────────────────────────

    public function verifyPayment(string $paymentId, float $expectedAmount): bool
    {
        $result = $this->verifyTransaction($paymentId);
        if (! $result) return false;
        return abs($result['amount'] - $expectedAmount) <= 0.05;
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Build a unique Paystack transaction reference.
     * Format: NEXUS-{invoice_ref}-{timestamp}
     * Must be unique per transaction attempt.
     */
    private function buildReference(Invoice $invoice): string
    {
        return 'NEXUS-' . $invoice->reference . '-' . time();
    }

    /**
     * Verify webhook signature.
     * Paystack signs with HMAC SHA512 using the secret key.
     * Header: x-paystack-signature
     */
    private function verifySignature(string $rawBody, string $signature): bool
    {
        if (empty($this->secretKey) || empty($signature)) {
            return false;
        }

        $computed = hash_hmac('sha512', $rawBody, $this->secretKey);

        return hash_equals($computed, $signature);
    }

    // ── Public key (needed by frontend for Popup mode) ────────

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}
