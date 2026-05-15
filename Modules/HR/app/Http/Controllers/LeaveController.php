<?php

declare(strict_types=1);

namespace Modules\HR\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\LeaveApplication;
use Modules\HR\app\Models\LeaveType;

class LeaveController extends Controller
{
    public function index()
    {
        $applications = LeaveApplication::with(['employee.user', 'leaveType'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($l) => [
                'id'         => $l->id,
                'employee'   => $l->employee?->user?->name,
                'type'       => $l->leaveType?->name,
                'start_date' => $l->start_date?->format('d M Y'),
                'end_date'   => $l->end_date?->format('d M Y'),
                'days'       => $l->days_requested,
                'status'     => $l->status,
            ]);

        return Inertia::render('HR/Pages/Leave/Index', [
            'applications' => $applications,
        ]);
    }

    public function create()
    {
        return Inertia::render('HR/Pages/Leave/Create', [
            'employees'   => Employee::with('user')->active()->get()->map(fn ($e) => [
                'id'   => $e->id,
                'name' => $e->user?->name,
            ]),
            'leave_types' => LeaveType::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|uuid|exists:hr_employees,id',
            'leave_type_id' => 'required|uuid|exists:hr_leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string',
        ]);

        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end   = \Carbon\Carbon::parse($validated['end_date']);
        $days  = $start->diffInWeekdays($end) + 1;

        LeaveApplication::create([
            ...$validated,
            'days_requested' => $days,
            'status'         => 'pending',
        ]);

        return redirect()
            ->route('hr.leave.index')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Leave application submitted',
                'message' => "Application submitted for {$days} day(s).",
            ]);
    }

    public function approve(LeaveApplication $leave)
    {
        $leave->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Leave approved',
        ]);
    }

    public function reject(Request $request, LeaveApplication $leave)
    {
        $leave->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Leave rejected',
        ]);
    }
}
