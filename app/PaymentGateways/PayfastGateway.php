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
        // Build payload in PayFast's required field order.
        // Empty fields must be excluded — they break signature validation.
        $nameFirst = $invoice->customer->contact_name ?? $invoice->customer->company_name;

        $data = array_filter([
            'merchant_id'      => $this->merchantId,
            'merchant_key'     => $this->merchantKey,
            'return_url'       => $returnUrl,
            'cancel_url'       => $cancelUrl,
            'notify_url'       => $notifyUrl,
            'name_first'       => $nameFirst,
            // name_last intentionally omitted — we don't split names, including it
            // as empty string breaks the signature
            'email_address'    => $invoice->customer->email,
            'm_payment_id'     => $invoice->id,
            'amount'           => number_format($amount, 2, '.', ''),
            'item_name'        => 'Invoice ' . $invoice->reference,
            'item_description' => 'Payment for invoice ' . $invoice->reference,
            'custom_str1'      => $invoice->id,
            'custom_str2'      => $invoice->reference,
        ], fn($v) => $v !== '' && $v !== null);

        // Append passphrase for signature only — removed from URL afterwards
        if ($this->passphrase !== '') {
            $data['passphrase'] = $this->passphrase;
        }

        // In initiatePayment() — exclude empties outbound
        $data['signature'] = $this->generateSignature($data, excludeEmpty: true);

        unset($data['passphrase']);

        return $this->baseUrl . '?' . http_build_query($data);
    }

    public function verifyPayment(string $paymentId, float $expectedAmount): bool
    {
        // PayFast verifies via ITN webhook — no simple status API available
        return true;
    }

    public function handleWebhook(array $payload, string $rawBody, string $signature): ?array
    {
        if (! $this->validateItn($payload)) {
            Log::warning('PayFast ITN validation failed', $payload);
            return null;
        }

        if (($payload['payment_status'] ?? '') !== 'COMPLETE') {
            return null;
        }

        return [
            'invoice_id' => $payload['custom_str1']   ?? null,
            'reference'  => $payload['custom_str2']   ?? null,
            'amount'     => (float) ($payload['amount_gross'] ?? 0),
            'payment_id' => $payload['pf_payment_id'] ?? null,
        ];
    }

    private function validateItn(array $payload): bool
    {
        $postedSignature = $payload['signature'] ?? '';
        $testPayload     = $payload;
        unset($testPayload['signature']);

        // For ITN validation: DO NOT strip null/empty fields.
        // PayFast includes all fields in their signature, with nulls as empty strings.
        // Convert nulls to empty strings to match what PayFast hashed.
        $testPayload = array_map(
            fn($v) => $v === null ? '' : $v,
            $testPayload
        );

        if ($this->passphrase !== '') {
            $testPayload['passphrase'] = $this->passphrase;
        }

        if ($this->generateSignature($testPayload) !== $postedSignature) {
            Log::warning('PayFast ITN signature mismatch');
            return false;
        }

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

    private function generateSignature(array $data, bool $excludeEmpty = false): string
    {
        $parts = [];
        foreach ($data as $key => $value) {
            if ($excludeEmpty && ($value === '' || $value === null)) {
                continue;
            }
            $parts[] = $key . '=' . urlencode((string) $value);
        }
        return md5(implode('&', $parts));
    }
}