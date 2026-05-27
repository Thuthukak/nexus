<?php

declare(strict_types=1);

namespace App\Services\Wizard\Steps;

use Illuminate\Support\Facades\DB;

class DatabaseSetupStep
{
    public function test(array $config): array
    {
        \Log::info('Testing DB connection with config', $config);
        try {
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_TIMEOUT => 5]
            );
            return ['success' => true, 'message' => 'Connection successful.'];
        } catch (\PDOException $e) {
            \Log::error('DB connection failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function writeToEnv(array $config): void
    {
        \Log::info('Writing DB config to .env', $config);
        $envPath = base_path('.env');
        $env     = file_get_contents($envPath);

        $replacements = [
            'DB_CONNECTION' => $config['driver']   ?? 'mysql',
            'DB_HOST'       => $config['host']      ?? '127.0.0.1',
            'DB_PORT'       => $config['port']      ?? '3306',
            'DB_DATABASE'   => $config['database']  ?? 'nexus',
            'DB_USERNAME'   => $config['username']  ?? 'root',
            'DB_PASSWORD'   => $config['password']  ?? '',
        ];

        foreach ($replacements as $key => $value) {
            if (preg_match("/^{$key}=/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $env);
        \Log::info('DB config written to .env');
    }
}
