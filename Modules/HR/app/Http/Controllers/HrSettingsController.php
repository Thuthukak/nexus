<?php

declare(strict_types=1);

namespace Modules\HR\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\HR\app\Services\HrSettingsService;

class HrSettingsController extends Controller
{
    public function __construct(
        private HrSettingsService $settings
    ) {}

    public function show()
    {
        return Inertia::render('HR/Pages/Settings/Index', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'document_expiry_warning_days' => 'required|integer|min:1|max:365',
            'company_name'                 => 'nullable|string|max:255',
            'payroll_period_start_day'     => 'required|integer|min:1|max:28',
        ]);

        foreach ($validated as $key => $value) {
            $this->settings->set($key, $value);
        }

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'HR settings saved',
        ]);
    }
}
