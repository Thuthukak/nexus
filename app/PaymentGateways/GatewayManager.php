<?php

declare(strict_types=1);

namespace App\PaymentGateways;

use App\Facades\Settings;
use App\PaymentGateways\Contracts\PaymentGatewayInterface;

class GatewayManager
{
    public function active(): ?PaymentGatewayInterface
    {
        $gateway = Settings::group('payments')->get('gateway', 'none');

        return match($gateway) {
            'payfast'  => app(PayfastGateway::class),
            'paystack' => app(PaystackGateway::class),
            default    => null,
        };
    }

    public function isConfigured(): bool
    {
        return $this->active() !== null;
    }

    public function isTestMode(): bool
    {
        return (bool) Settings::group('payments')->get('test_mode', true);
    }

    public function gatewayName(): string
    {
        return Settings::group('payments')->get('gateway', 'none');
    }
}
