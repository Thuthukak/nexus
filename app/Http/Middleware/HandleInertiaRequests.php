<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user'        => $request->user(),
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')
                    : [],
            ],
            'flash' => [
                'toast'   => $request->session()->get('toast'),
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
            'app' => [
                'name' => config('app.name'),
            ],
            'theme' => [
                'primary'      => '#1E3A5F',
                'primary_text' => '#FFFFFF',
                'secondary'    => '#2E86AB',
                'accent'       => '#F39C12',
                'sidebar_bg'   => '#1E3A5F',
                'sidebar_text' => '#CBD5E1',
                'surface'      => '#FFFFFF',
                'background'   => '#F8F9FA',
                'text'         => '#2C3E50',
            ],
        ]);
    }
}
