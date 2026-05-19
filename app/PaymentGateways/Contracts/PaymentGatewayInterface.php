<?php

declare(strict_types=1);

namespace App\PaymentGateways\Contracts;

use Modules\Financial\app\Models\Invoice;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment — returns a redirect URL to the gateway.
     */
    public function initiatePayment(
        Invoice $invoice,
        float   $amount,
        string  $returnUrl,
        string  $cancelUrl,
        string  $notifyUrl,
    ): string;

    /**
     * Verify a payment using the gateway's reference.
     * Returns true if payment is confirmed.
     */
    public function verifyPayment(string $reference, float $expectedAmount): bool;

    /**
     * Handle webhook/ITN from the gateway.
     * Returns the invoice reference and amount paid if valid, null if invalid.
     */
    public function handleWebhook(array $payload, string $rawBody, string $signature): ?array;

    /**
     * Returns the gateway name for logging and display.
     */
    public function getName(): string;
}
