<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WizardMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $path       = $request->path();
        $isInstall  = str_starts_with($path, 'install');
        $isAsset    = str_starts_with($path, 'build')
            || str_starts_with($path, 'storage')
            || str_starts_with($path, 'up');
        $isPayPath  = str_starts_with($path, 'pay')
            || str_starts_with($path, 'webhooks');
        $isStudent  = str_starts_with($path, 'student');

        if ($isAsset || $isPayPath || $isStudent) {
            return $next($request);
        }

        // Force file sessions during install so DB not required
        if ($isInstall) {
            config([
                'session.driver'   => 'file',
                'session.connection'=> null,
            ]);
        }

        $installed = $this->isInstalled();

        if (! $installed && ! $isInstall) {
            return redirect('/install/step/1');
        }

        if ($installed && $isInstall) {
            return redirect('/dashboard');
        }

        return $next($request);
    }

    private function isInstalled(): bool
    {
        $envPath = base_path('.env');

        // No .env at all — definitely not installed
        // Copy .env.example if it exists to prevent Laravel boot errors
        if (! file_exists($envPath)) {
            $example = base_path('.env.example');
            if (file_exists($example)) {
                copy($example, $envPath);
            }
            return false;
        }

        $env = file_get_contents($envPath);
        if (preg_match('/^APP_INSTALLED=(.+)$/m', $env, $matches)) {
            return strtolower(trim($matches[1])) === 'true';
        }

        return false;
    }
}
