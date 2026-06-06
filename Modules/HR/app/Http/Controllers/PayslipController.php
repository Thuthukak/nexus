<?php

declare(strict_types=1);

namespace Modules\HR\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\Payslip;
use Modules\HR\app\Services\PayslipService;

class PayslipController extends Controller
{
    public function __construct(
        private PayslipService $service
    ) {}

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'period_year'  => 'required|integer|min:2000|max:2099',
            'period_month' => 'required|integer|min:1|max:12',
            'file'         => 'required|file|mimes:pdf|max:10240',
            'gross_amount' => 'nullable|numeric|min:0',
            'net_amount'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ]);

        $this->service->store(
            $employee,
            $request->file('file'),
            $validated,
            $request->user()->id,
        );

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Payslip uploaded',
            'message' => \Carbon\Carbon::createFromDate(
                $validated['period_year'],
                $validated['period_month'],
                1
            )->format('F Y') . ' payslip saved.',
        ]);
    }

    public function download(Employee $employee, Payslip $payslip)
    {
        abort_if($payslip->employee_id !== $employee->id, 403);
        return $this->service->download($payslip);
    }

    public function downloadOwn(Request $request, Payslip $payslip)
    {
        // Employee downloading their own payslip from profile
        $employee = Employee::where('user_id', $request->user()->id)->first();
        abort_if(! $employee || $payslip->employee_id !== $employee->id, 403);

        return $this->service->download($payslip);
    }

    public function destroy(Employee $employee, Payslip $payslip)
    {
        abort_if($payslip->employee_id !== $employee->id, 403);
        $this->service->delete($payslip);

        return back()->with('toast', [
            'type'  => 'success',
            'title' => 'Payslip deleted',
        ]);
    }
}
