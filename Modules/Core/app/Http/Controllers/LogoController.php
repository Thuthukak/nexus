<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LogoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // Delete old logo if exists
        $existing = Settings::group('general')->get('logo_path');
        if ($existing && \Storage::disk('public')->exists($existing)) {
            \Storage::disk('public')->delete($existing);
        }

        $path = $request->file('logo')->store('logos', 'public');

        Settings::group('general')->set('logo_path', $path);
        Settings::group('general')->set('logo_url', \Storage::disk('public')->url($path));

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Logo uploaded',
            'message' => 'Your logo has been saved.',
        ]);
    }

    public function destroy()
    {
        $path = Settings::group('general')->get('logo_path');
        if ($path && \Storage::disk('public')->exists($path)) {
            \Storage::disk('public')->delete($path);
        }

        Settings::group('general')->forget('logo_path');
        Settings::group('general')->forget('logo_url');

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Logo removed',
        ]);
    }
}
