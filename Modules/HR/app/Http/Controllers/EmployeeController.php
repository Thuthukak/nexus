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
            'guard'             => 'web',
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
        $employee->load('department', 'jobTitle', 'user');
        $documents = \Modules\HR\app\Models\HrDocument::where('employee_id', $employee->id)
            ->with('uploadedBy')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($d) => [
                'id'            => $d->id,
                'name'          => $d->name,
                'type'          => $d->type,
                'type_label'    => \Modules\HR\app\Models\HrDocument::TYPES[$d->type] ?? $d->type,
                'file_name'     => $d->file_name,
                'file_size'     => $d->file_size_formatted,
                'visibility'    => $d->visibility,
                'expiry_date'   => $d->expiry_date?->format('d M Y'),
                'expiry_raw'    => $d->expiry_date?->format('Y-m-d'),
                'is_expired'    => $d->is_expired,
                'is_expiring'   => $d->isExpiringSoon(),
                'notes'         => $d->notes,
                'customer_id'   => $d->customer_id,
                'uploaded_by'   => $d->uploadedBy?->name,
                'created_at'    => $d->created_at->format('d M Y'),
            ]);

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
                'file_name'    => $p->file_name,
                'notes'        => $p->notes,
                'created_at'   => $p->created_at->format('d M Y'),
            ]);

            $customers = \Modules\Financial\app\Models\Customer::active()
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

            return Inertia::render('HR/Pages/Employees/Show', [
                'documents' => $documents,
                'payslips'  => $payslips,
                'customers' => $customers,
                'docTypes'  => \Modules\HR\app\Models\HrDocument::TYPES,
                'employee' => $employee,
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
