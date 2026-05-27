<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ModuleRegistryService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleAccessMiddleware
{
    public function __construct(
        private ModuleRegistryService $registry
    ) {}

    public function handle(Request $request, Closure $next, string $moduleName): mixed
    {
        if (! $this->checkModuleEnabled($moduleName)) {
            if ($request->header('X-Inertia')) {
                return inertia('Core/Pages/ModuleDisabled', [
                    'module' => $moduleName,
                ])->toResponse($request)->setStatusCode(403);
            }

            abort(403, "The {$moduleName} module is not active on this installation.");
        }

        return $next($request);
    }

    private function checkModuleEnabled(string $moduleName): bool
    {
        // Always allow in local dev
        if (app()->environment('local')) return true;

        // Check directly from DB — bypass any in-memory cache
        // so disabling a module takes effect on the very next request
        try {
            if (! Schema::hasTable('installed_modules')) return true;

            $module = DB::table('installed_modules')
                ->where('name', $moduleName)
                ->first();

            if (! $module) return false;

            return (bool) $module->is_enabled;
        } catch (\Throwable) {
            // If DB is unavailable, fall back to registry
            return $this->registry->isEnabled($moduleName);
        }
    }
}
