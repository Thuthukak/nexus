<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WizardMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $installed = config('app.installed', env('APP_INSTALLED', false));
        $isInstall = str_starts_with($request->path(), 'install');
        $isAsset   = str_starts_with($request->path(), 'build')
            || str_starts_with($request->path(), 'storage');

        if ($isAsset) {
            return $next($request);
        }

        // Not installed — redirect everything to wizard
        if (! $installed && ! $isInstall) {
            return redirect('/install/step/1');
        }

        // Already installed — block access to wizard
        if ($installed && $isInstall) {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
