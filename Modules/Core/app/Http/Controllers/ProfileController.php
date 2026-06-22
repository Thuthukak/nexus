<?php

declare(strict_types=1);

namespace Modules\Core\app\Http\Controllers;

use App\Rules\StrongPassword;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Services\ModuleRegistryService;

class ProfileController extends Controller
{
    // ── Main profile page ─────────────────────────────────────

    public function show(Request $request)
    {
        $user           = $request->user();
        $activeModules  = DB::table('installed_modules')
            ->where('is_enabled', true)
            ->pluck('name')
            ->toArray();

        $hrActive     = in_array('HR', $activeModules);
        $lmsActive    = in_array('LMS', $activeModules);
        $eventsActive = in_array('Events', $activeModules);

        // ── HR data ───────────────────────────────────────────
        $employee    = null;
        $payslips    = [];
        $documents   = [];
        $leaveHistory= [];

        if ($hrActive) {
            $employee = \Modules\HR\app\Models\Employee::where('user_id', $user->id)
                ->with(['department', 'jobTitle'])
                ->first();

            if ($employee) {
                $payslips = \Modules\HR\app\Models\Payslip::where('employee_id', $employee->id)
                    ->orderByDesc('period_year')
                    ->orderByDesc('period_month')
                    ->get()
                    ->map(fn ($p) => [
                        'id'           => $p->id,
                        'period_label' => $p->period_label,
                        'period_year'  => $p->period_year,
                        'period_month' => $p->period_month,
                        'gross_amount' => $p->gross_amount,
                        'net_amount'   => $p->net_amount,
                        'created_at'   => $p->created_at->format('d M Y'),
                    ]);

                $documents = \Modules\HR\app\Models\HrDocument::where('employee_id', $employee->id)
                    ->where('visibility', 'internal')
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(fn ($d) => [
                        'id'          => $d->id,
                        'name'        => $d->name,
                        'type'        => $d->type,
                        'type_label'  => \Modules\HR\app\Models\HrDocument::TYPES[$d->type] ?? $d->type,
                        'file_name'   => $d->file_name,
                        'file_size'   => $d->file_size_formatted,
                        'expiry_date' => $d->expiry_date?->format('d M Y'),
                        'is_expired'  => $d->is_expired,
                        'created_at'  => $d->created_at->format('d M Y'),
                    ]);

                $leaveHistory = \Modules\HR\app\Models\LeaveApplication::where('employee_id', $employee->id)
                    ->with('leaveType')
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get()
                    ->map(fn ($l) => [
                        'id'         => $l->id,
                        'type'       => $l->leaveType?->name ?? '—',
                        'start_date' => $l->start_date?->format('d M Y'),
                        'end_date'   => $l->end_date?->format('d M Y'),
                        'days'       => $l->days,
                        'status'     => $l->status,
                        'reason'     => $l->reason,
                    ]);
            }
        }

        // ── LMS data ──────────────────────────────────────────
        $enrollments = [];

        if ($lmsActive) {
            $enrollments = \Modules\LMS\app\Models\Enrollment::with([
                'cohort.course',
                'certificate',
            ])
            ->where('student_id', $user->id)
            ->where('status', '!=', 'withdrawn')
            ->orderByDesc('enrolled_at')
            ->get()
            ->map(fn ($e) => [
                'id'              => $e->id,
                'course_title'    => $e->cohort->course->title,
                'cohort_name'     => $e->cohort->name,
                'status'          => $e->status,
                'progress'        => $e->progress_percent,
                'enrolled_at'     => $e->enrolled_at?->format('d M Y'),
                'completed_at'    => $e->completed_at?->format('d M Y'),
                'has_certificate' => $e->certificate !== null,
                'thumbnail_url'   => $e->cohort->course->thumbnail_path
                    ? asset('storage/' . $e->cohort->course->thumbnail_path)
                    : null,
            ]);
        }

        // ── Events / Ticket orders ────────────────────────────
        $ticketOrders = [];

        if ($eventsActive) {
            $ticketOrders = \Modules\Events\app\Models\Order::where('customer_email', $user->email)
                ->where('status', 'paid')
                ->with(['event', 'items', 'tickets'])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($o) => [
                    'id'            => $o->id,
                    'reference'     => $o->reference,
                    'event_title'   => $o->event->title,
                    'event_date'    => $o->event->starts_at->format('d M Y'),
                    'event_venue'   => $o->event->venue,
                    'total'         => $o->total,
                    'tickets_count' => $o->tickets->count(),
                    'paid_at'       => $o->paid_at?->format('d M Y'),
                    'event_id'      => $o->event->id,
                ]);
        }

        // ── Recent activity ───────────────────────────────────
        $recentActivity = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->where('causer_type', \App\Models\User::class)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($a) => [
                'description' => $a->description,
                'log_name'    => $a->log_name,
                'created_at'  => $a->created_at->diffForHumans(),
            ]);

        return Inertia::render('Core/Pages/Profile/Index', [
            'user' => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'roles'              => $user->getRoleNames(),
                'last_login_at'      => $user->last_login_at?->format('d M Y H:i'),
                'email_verified_at'  => $user->email_verified_at?->format('d M Y'),
                'created_at'         => $user->created_at->format('d M Y'),
                'notification_preferences' => $user->notification_preferences ?? [],
            ],
            'employee'        => $employee ? [
                'id'         => $employee->id,
                'number'     => $employee->employee_number,
                'department' => $employee->department?->name,
                'job_title'  => $employee->jobTitle?->name,
                'start_date' => $employee->start_date?->format('d M Y'),
            ] : null,
            'payslips'        => $payslips,
            'documents'       => $documents,
            'leaveHistory'    => $leaveHistory,
            'enrollments'     => $enrollments,
            'ticketOrders'    => $ticketOrders,
            'recentActivity'  => $recentActivity,
            'activeModules'   => $activeModules,
            'modules' => [
                'hr'     => $hrActive,
                'lms'    => $lmsActive,
                'events' => $eventsActive,
            ],
        ]);
    }

    // ── Update personal details ───────────────────────────────

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Profile updated',
        ]);
    }

    // ── Change password ───────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', new StrongPassword()],
            'password_confirmation' => 'required',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Password updated',
        ]);
    }

    // ── Notification preferences ──────────────────────────────

    public function notificationPreferences(Request $request, ModuleRegistryService $registry)
    {
        $activeModules = $registry->getEnabledModules();

        $allTypes = [
            'Core' => [
                ['key' => 'user.created', 'label' => 'New User Created'],
            ],
            'Financial' => [
                ['key' => 'invoice.approved', 'label' => 'Invoice Approved'],
                ['key' => 'invoice.paid',     'label' => 'Invoice Paid'],
                ['key' => 'invoice.overdue',  'label' => 'Invoice Overdue'],
            ],
            'HR' => [
                ['key' => 'leave.submitted', 'label' => 'Leave Application'],
                ['key' => 'leave.approved',  'label' => 'Leave Approved'],
                ['key' => 'leave.rejected',  'label' => 'Leave Rejected'],
            ],
            'Bookings' => [
                ['key' => 'booking.confirmed', 'label' => 'Booking Confirmed'],
                ['key' => 'booking.cancelled', 'label' => 'Booking Cancelled'],
            ],
            'Events' => [
                ['key' => 'event.created', 'label' => 'New Event Created'],
                ['key' => 'event.updated', 'label' => 'Event Updated'     ],
                ['key' => 'event.cancelled', 'label' => 'Event Cancelled' ],
            ],
            'LMS' => [
                ['key' => 'course.assigned', 'label' => 'Course Assigned' ],
                ['key' => 'course.completed', 'label' => 'Course Completed' ],
            ],
        ];

        $notificationTypes = collect($allTypes)
            ->filter(fn ($_, $module) => $module === 'Core' || in_array($module, $activeModules))
            ->map(fn ($types, $module) => ['module' => $module, 'types' => $types])
            ->values();

        return Inertia::render('Core/Pages/Profile/Notifications', [
            'preferences'       => $request->user()->notification_preferences ?? (object)[],
            'notificationTypes' => $notificationTypes,
        ]);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $validated = $request->validate([
            'preferences'          => 'array',
            'preferences.*'        => 'array',
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

    // ── My Payslips (download) ────────────────────────────────

    public function myPayslips(Request $request)
    {
        // Redirect to profile — payslips now live on profile page
        return redirect()->route('profile.show');
    }

    public function downloadPayslip(Request $request, \Modules\HR\app\Models\Payslip $payslip)
    {
        $employee = \Modules\HR\app\Models\Employee::where('user_id', $request->user()->id)->first();
        abort_if(! $employee || $payslip->employee_id !== $employee->id, 403);

        return app(\Modules\HR\app\Services\PayslipService::class)->download($payslip);
    }

    public function downloadDocument(Request $request, \Modules\HR\app\Models\HrDocument $document)
    {
        $employee = \Modules\HR\app\Models\Employee::where('user_id', $request->user()->id)->first();
        abort_if(! $employee || $document->employee_id !== $employee->id, 403);

        return app(\Modules\HR\app\Services\HrDocumentService::class)->download($document);
    }
}
