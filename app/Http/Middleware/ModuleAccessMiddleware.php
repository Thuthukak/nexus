<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ModuleRegistryService;
use Closure;
use Illuminate\Http\Request;

class ModuleAccessMiddleware
{
    public function __construct(
        private ModuleRegistryService $registry
    ) {}

    public function handle(Request $request, Closure $next, string $moduleName): mixed
    {
        if (! $this->registry->isEnabled($moduleName)) {
            if ($request->header('X-Inertia')) {
                return inertia('Core/Pages/ModuleDisabled', [
                    'module' => $moduleName,
                ])->toResponse($request)->setStatusCode(403);
            }

            abort(403, "The {$moduleName} module is not active on this installation.");
        }

        return $next($request);
    }
}
