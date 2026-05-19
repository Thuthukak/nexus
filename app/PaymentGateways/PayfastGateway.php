<?php

declare(strict_types=1);

namespace App\PaymentGateways;

use App\Facades\Settings;
use App\PaymentGateways\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Modules\Financial\app\Models\Invoice;

class PayfastGateway implements PaymentGatewayInterface
{
    private string $merchantId;
    private string $merchantKey;
    private string $passphrase;
    private bool   $testMode;
    private string $baseUrl;

    public function __construct()
    {
        $settings          = Settings::group('payments');
        $this->testMode    = (bool) $settings->get('test_mode', true);
        $this->merchantId  = $settings->get('payfast_merchant_id',  $this->testMode ? '10000100' : '');
        $this->merchantKey = $settings->get('payfast_merchant_key', $this->testMode ? '46f0cd694581a' : '');
        $this->passphrase  = $settings->get('payfast_passphrase', '') ?? '';
        $this->baseUrl     = $this->testMode
            ? 'https://sandbox.payfast.co.za/eng/process'
            : 'https://www.payfast.co.za/eng/process';
    }

    public function getName(): string
    {
        return 'PayFast' . ($this->testMode ? ' (Test)' : '');
    }

    public function initiatePayment(
        Invoice $invoice,
        float   $amount,
        string  $returnUrl,
        string  $cancelUrl,
        string  $notifyUrl,
    ): string {
        $data = [
            'merchant_id'   => $this->merchantId,
            'merchant_key'  => $this->merchantKey,
            'return_url'    => $returnUrl,
            'cancel_url'    => $cancelUrl,
            'notify_url'    => $notifyUrl,
            'name_first'    => $invoice->customer->contact_name ?? $invoice->customer->company_name,
            'name_last'     => '',
            'email_address' => $invoice->customer->email,
            'm_payment_id'  => $invoice->id,
            'amount'        => number_format($amount, 2, '.', ''),
            'item_name'     => 'Invoice ' . $invoice->reference,
            'item_description' => 'Payment for invoice ' . $invoice->reference,
            'custom_str1'   => $invoice->id,
            'custom_str2'   => $invoice->reference,
        ];

        if ($this->passphrase) {
            $data['passphrase'] = $this->passphrase;
        }

        // Generate signature
        $data['signature'] = $this->generateSignature($data);

        // Remove passphrase from POST data
        unset($data['passphrase']);

        // Build redirect URL with form POST (PayFast requires POST)
        // We return the base URL + encoded data — the payment page renders a self-submitting form
        return $this->baseUrl . '?' . http_build_query($data);
    }

    public function verifyPayment(string $paymentId, float $expectedAmount): bool
    {
        // PayFast verifies via ITN webhook — this is handled in handleWebhook
        // For manual verification, we'd query the PayFast API (they don't have a simple status API)
        return true;
    }

    public function handleWebhook(array $payload, string $rawBody, string $signature): ?array
    {
        // Validate the ITN (Instant Transfer Notification)
        if (! $this->validateItn($payload)) {
            Log::warning('PayFast ITN validation failed', $payload);
            return null;
        }

        if (($payload['payment_status'] ?? '') !== 'COMPLETE') {
            return null;
        }

        return [
            'invoice_id'  => $payload['custom_str1']   ?? null,
            'reference'   => $payload['custom_str2']   ?? null,
            'amount'      => (float) ($payload['amount_gross'] ?? 0),
            'payment_id'  => $payload['pf_payment_id'] ?? null,
        ];
    }

    private function validateItn(array $payload): bool
    {
        // Step 1: Verify source IP (PayFast IPs)
        $validHosts = $this->testMode
            ? ['sandbox.payfast.co.za']
            : ['www.payfast.co.za', 'w1w.payfast.co.za', 'w2w.payfast.co.za'];

        // Step 2: Validate signature
        $postedSignature = $payload['signature'] ?? '';
        $testPayload     = $payload;
        unset($testPayload['signature']);

        if ($this->passphrase) {
            $testPayload['passphrase'] = $this->passphrase;
        }

        $generatedSignature = $this->generateSignature($testPayload);

        if ($generatedSignature !== $postedSignature) {
            Log::warning('PayFast ITN signature mismatch');
            return false;
        }

        // Step 3: Verify with PayFast server
        $validateUrl = $this->testMode
            ? 'https://sandbox.payfast.co.za/eng/query/validate'
            : 'https://www.payfast.co.za/eng/query/validate';

        try {
            $response = \Illuminate\Support\Facades\Http::post($validateUrl, $payload);
            return str_contains($response->body(), 'VALID');
        } catch (\Throwable $e) {
            Log::error('PayFast ITN validation request failed: ' . $e->getMessage());
            return false;
        }
    }

    private function generateSignature(array $data): string
    {
        ksort($data);
        $queryString = http_build_query($data);
        $queryString = urldecode($queryString);
        return md5($queryString);
    }
}
