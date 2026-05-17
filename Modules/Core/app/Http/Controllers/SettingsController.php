<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function appearance()
    {
        return Inertia::render('Core/Pages/Settings/Appearance', [
            'theme' => Settings::group('theme')->all(),
        ]);
    }

    public function updateAppearance(Request $request)
    {
        $validated = $request->validate([
            'primary'      => 'required|string|max:7',
            'primary_text' => 'required|string|max:7',
            'secondary'    => 'required|string|max:7',
            'accent'       => 'required|string|max:7',
            'sidebar_bg'   => 'required|string|max:7',
            'sidebar_text' => 'required|string|max:7',
            'surface'      => 'required|string|max:7',
            'background'   => 'required|string|max:7',
            'text'         => 'required|string|max:7',
        ]);

        $settings = Settings::group('theme');
        foreach ($validated as $key => $value) {
            $settings->set($key, $value);
        }

        return redirect()
            ->back()
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Theme saved',
                'message' => 'Your colour palette has been updated.',
            ]);
    }

    public function general()
    {
        return Inertia::render('Core/Pages/Settings/General', [
            'settings' => [
                'app_name' => Settings::group('general')->get('app_name', config('app.name')),
                'timezone' => Settings::group('general')->get('timezone', 'Africa/Johannesburg'),
            ],
        ]);
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'timezone' => 'required|string|timezone',
        ]);

        $settings = Settings::group('general');
        foreach ($validated as $key => $value) {
            $settings->set($key, $value);
        }

        return redirect()
            ->back()
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Settings saved',
            ]);
    }
}
