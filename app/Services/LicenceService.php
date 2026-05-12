<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LicenceService
{
    private ?array $payload = null;

    public function __construct()
    {
        $this->payload = $this->load();
    }

    private function load(): ?array
    {
        // In development, return a permissive dev licence
        if (app()->environment('local')) {
            return [
                'licensee'   => 'Nexus Development',
                'email'      => 'dev@nexus.local',
                'domain'     => 'localhost',
                'modules'    => ['Core', 'Financial', 'HR', 'Bookings'],
                'tier'       => 'professional',
                'max_users'  => 999,
                'issued_at'  => now()->subDay()->toISOString(),
                'expires_at' => now()->addYear()->toISOString(),
                'dev'        => true,
            ];
        }

        $keyPath = storage_path('licence/key.txt');
        if (! file_exists($keyPath)) {
            return null;
        }

        return Cache::remember('licence.payload', 3600, function () use ($keyPath) {
            return $this->validate(file_get_contents($keyPath));
        });
    }

    private function validate(string $rawKey): ?array
    {
        $parts = explode('.', trim($rawKey));
        if (count($parts) !== 2) return null;

        [$encodedPayload, $encodedSignature] = $parts;

        $publicKeyPem = config('licence.public_key');
        if (! $publicKeyPem) return null;

        $publicKey = openssl_pkey_get_public($publicKeyPem);
        if (! $publicKey) return null;

        $payload   = base64_decode(strtr($encodedPayload,  '-_', '+/'));
        $signature = base64_decode(strtr($encodedSignature, '-_', '+/'));

        $result = openssl_verify($payload, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($result !== 1) return null;

        return json_decode($payload, true);
    }

    public function isValid(): bool
    {
        if (! $this->payload) return false;
        if (Carbon::parse($this->payload['expires_at'])->isPast()) return false;
        if (! app()->environment('local')) {
            $domain = parse_url(config('app.url'), PHP_URL_HOST);
            if ($this->payload['domain'] !== $domain) return false;
        }
        return true;
    }

    public function isModuleAllowed(string $module): bool
    {
        return $this->isValid()
            && in_array($module, $this->payload['modules'] ?? [], true);
    }

    public function getAllowedModules(): array
    {
        return $this->payload['modules'] ?? [];
    }

    public function getExpiresAt(): ?Carbon
    {
        return isset($this->payload['expires_at'])
            ? Carbon::parse($this->payload['expires_at'])
            : null;
    }

    public function getLicensee(): string
    {
        return $this->payload['licensee'] ?? 'Unknown';
    }

    public function getMaxUsers(): int
    {
        return $this->payload['max_users'] ?? 0;
    }

    public function getTier(): string
    {
        return $this->payload['tier'] ?? 'unknown';
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }
}
