<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
                'name'     => $this->appName(),
                'logo_url' => $this->logoUrl(),
            ],
            'theme' => $this->theme(),
        ]);
    }

    private function theme(): array
    {
        $defaults = [
            'primary'      => '#1E3A5F',
            'primary_text' => '#FFFFFF',
            'secondary'    => '#2E86AB',
            'accent'       => '#F39C12',
            'sidebar_bg'   => '#1E3A5F',
            'sidebar_text' => '#CBD5E1',
            'surface'      => '#FFFFFF',
            'background'   => '#F8F9FA',
            'text'         => '#2C3E50',
        ];

        try {
            if (! Schema::hasTable('settings')) return $defaults;
            $stored = Settings::group('theme')->all();
            return array_merge($defaults, array_filter($stored));
        } catch (\Throwable) {
            return $defaults;
        }
    }

    private function appName(): string
    {
        try {
            if (! Schema::hasTable('settings')) return config('app.name');
            return Settings::group('general')->get('app_name', config('app.name'));
        } catch (\Throwable) {
            return config('app.name');
        }
    }

    private function logoUrl(): ?string
    {
        try {
            if (! Schema::hasTable('settings')) return null;
            return Settings::group('general')->get('logo_url');
        } catch (\Throwable) {
            return null;
        }
    }
}
