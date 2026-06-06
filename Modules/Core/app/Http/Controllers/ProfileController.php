<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('Core/Pages/Profile/Show', [
            'user' => [
                'id'    => $request->user()->id,
                'name'  => $request->user()->name,
                'email' => $request->user()->email,
                'roles' => $request->user()->getRoleNames(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        $request->user()->update($validated);

        return redirect()
            ->back()
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Profile updated',
            ]);
    }

    public function myPayslips(\Illuminate\Http\Request $request)
    {
        $employee = \Modules\HR\app\Models\Employee::where('user_id', $request->user()->id)
            ->first();

        if (! $employee) {
            return \Inertia\Inertia::render('Core/Pages/Profile/Payslips', [
                'payslips' => [],
            ]);
        }

        $payslips = \Modules\HR\app\Models\Payslip::where('employee_id', $employee->id)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'period_label' => $p->period_label,
                'gross_amount' => $p->gross_amount,
                'net_amount'   => $p->net_amount,
                'created_at'   => $p->created_at->format('d M Y'),
            ]);

        return \Inertia\Inertia::render('Core/Pages/Profile/Payslips', [
            'payslips' => $payslips,
        ]);
    }

    public function notificationPreferences(Request $request)
    {
        return Inertia::render('Core/Pages/Profile/Notifications', [
            'preferences' => $request->user()->notification_preferences ?? [],
        ]);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $validated = $request->validate([
            'preferences'        => 'array',
            'preferences.*'      => 'array',
            'preferences.*.email'  => 'boolean',
            'preferences.*.in_app' => 'boolean',
        ]);

        $request->user()->update([
            'notification_preferences' => $validated['preferences'],
        ]);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Preferences saved',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->back()
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Password updated',
            ]);
    }
}
