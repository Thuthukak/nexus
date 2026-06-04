<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Must be authenticated via customer guard
        if (! Auth::guard('customer')->check()) {
            return redirect()->route('portal.login')
                ->with('message', 'Please log in to access the client portal.');
        }

        $user = Auth::guard('customer')->user();

        // Must be active
        if (! $user->is_active) {
            Auth::guard('customer')->logout();
            return redirect()->route('portal.login')
                ->with('error', 'Your portal access has been suspended. Please contact us.');
        }

        // Bind customer guard user to request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
