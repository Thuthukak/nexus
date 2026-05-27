<?php

declare(strict_types=1);

namespace App\Services\Wizard\Steps;

class EnvironmentCheckStep
{
    public function check(): array
    {
        $checks = [];

        // PHP version
        $phpVersion = PHP_VERSION;
        $checks[]   = [
            'label'  => 'PHP Version (8.3+)',
            'status' => version_compare($phpVersion, '8.3.0', '>=') ? 'pass' : 'fail',
            'value'  => $phpVersion,
        ];

        // Required extensions
        $extensions = [
            'openssl', 'pdo', 'pdo_mysql', 'mbstring',
            'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'gd',
        ];

        foreach ($extensions as $ext) {
            $checks[] = [
                'label'  => "Extension: {$ext}",
                'status' => extension_loaded($ext) ? 'pass' : 'fail',
                'value'  => extension_loaded($ext) ? 'Loaded' : 'Missing',
            ];
        }

        // Folder permissions
        $folders = [
            'storage/app'        => storage_path('app'),
            'storage/logs'       => storage_path('logs'),
            'storage/framework'  => storage_path('framework'),
            'bootstrap/cache'    => base_path('bootstrap/cache'),
        ];

        foreach ($folders as $label => $path) {
            $writable = is_writable($path);
            $checks[] = [
                'label'  => "Writable: {$label}",
                'status' => $writable ? 'pass' : 'fail',
                'value'  => $writable ? 'Writable' : 'Not writable',
            ];
        }

        // .env file
        $envWritable = is_writable(base_path('.env'));
        $checks[]    = [
            'label'  => '.env file writable',
            'status' => $envWritable ? 'pass' : 'warn',
            'value'  => $envWritable ? 'Writable' : 'Not writable — manual config needed',
        ];

        $allPassed = collect($checks)->every(fn ($c) => $c['status'] !== 'fail');

        return [
            'checks'     => $checks,
            'all_passed' => $allPassed,
        ];
    }
}
