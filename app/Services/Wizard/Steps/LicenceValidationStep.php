<?php

declare(strict_types=1);

namespace App\Services\Wizard\Steps;

use App\Services\LicenceService;

class LicenceValidationStep
{
    public function validate(string $licenceKey): array
    {
        // Store key temporarily and validate
        $storagePath = storage_path('licence');
        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $keyFile = $storagePath . '/key.txt';
        file_put_contents($keyFile, trim($licenceKey));

        // Force re-instantiation to pick up new key
        $service = new LicenceService();

        if (! $service->isValid()) {
            unlink($keyFile);
            return [
                'valid'   => false,
                'message' => 'Invalid licence key. Please check the key and try again.',
            ];
        }

        return [
            'valid'          => true,
            'licensee'       => $service->getLicensee(),
            'expires_at'     => $service->getExpiresAt()?->format('d M Y'),
            'modules'        => $service->getAllowedModules(),
            'max_users'      => $service->getMaxUsers(),
            'tier'           => $service->getTier(),
        ];
    }
}
