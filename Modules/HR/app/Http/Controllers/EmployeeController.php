<?php

declare(strict_types=1);

namespace Modules\HR\app\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Modules\HR\app\Models\Department;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\JobTitle;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['user', 'department', 'jobTitle'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($e) => [
                'id'              => $e->id,
                'name'            => $e->user?->name,
                'email'           => $e->user?->email,
                'department'      => $e->department?->name,
                'job_title'       => $e->jobTitle?->name,
                'employment_type' => $e->employment_type,
                'status'          => $e->status,
                'start_date'      => $e->start_date?->format('d M Y'),
            ]);

        return Inertia::render('HR/Pages/Employees/Index', [
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        return Inertia::render('HR/Pages/Employees/Create', [
            'departments' => Department::where('is_active', true)->get(['id', 'name']),
            'job_titles'  => JobTitle::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'department_id'   => 'nullable|uuid',
            'job_title_id'    => 'nullable|uuid',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'start_date'      => 'required|date',
            'phone'           => 'nullable|string',
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make('Welcome@1'),
            'guard'             => 'internal',
            'email_verified_at' => now(),
        ]);

        $user->assignRole('Staff');

        $employee = Employee::create([
            'user_id'         => $user->id,
            'department_id'   => $validated['department_id'] ?? null,
            'job_title_id'    => $validated['job_title_id']  ?? null,
            'employment_type' => $validated['employment_type'],
            'start_date'      => $validated['start_date'],
            'phone'           => $validated['phone'] ?? null,
            'employee_number' => $this->nextEmployeeNumber(),
        ]);

        return redirect()
            ->route('hr.employees.show', $employee)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Employee added',
                'message' => "{$user->name} has been added to the system.",
            ]);
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'jobTitle', 'leaveApplications.leaveType']);

        return Inertia::render('HR/Pages/Employees/Show', [
            'employee' => [
                'id'              => $employee->id,
                'name'            => $employee->user?->name,
                'email'           => $employee->user?->email,
                'employee_number' => $employee->employee_number,
                'department'      => $employee->department?->name,
                'job_title'       => $employee->jobTitle?->name,
                'employment_type' => $employee->employment_type,
                'status'          => $employee->status,
                'start_date'      => $employee->start_date?->format('d M Y'),
                'phone'           => $employee->phone,
                'leave'           => $employee->leaveApplications->map(fn ($l) => [
                    'id'         => $l->id,
                    'type'       => $l->leaveType?->name,
                    'start_date' => $l->start_date?->format('d M Y'),
                    'end_date'   => $l->end_date?->format('d M Y'),
                    'days'       => $l->days_requested,
                    'status'     => $l->status,
                ]),
            ],
        ]);
    }

    private function nextEmployeeNumber(): string
    {
        $last = Employee::withTrashed()->max('employee_number');
        if (! $last) return 'EMP-0001';
        $n = (int) str_replace('EMP-', '', $last);
        return 'EMP-' . str_pad((string) ($n + 1), 4, '0', STR_PAD_LEFT);
    }
}
