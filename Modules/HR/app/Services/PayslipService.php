<?php

declare(strict_types=1);

namespace Modules\HR\app\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\HR\app\Models\Employee;
use Modules\HR\app\Models\Payslip;

class PayslipService
{
    private const DISK = 'local';
    private const DIR  = 'private/hr/payslips';

    public function store(
        Employee     $employee,
        UploadedFile $file,
        array        $data,
        int          $userId,
    ): Payslip {
        // Delete existing payslip for same period if exists
        $existing = Payslip::where('employee_id', $employee->id)
            ->where('period_year',  $data['period_year'])
            ->where('period_month', $data['period_month'])
            ->first();

        if ($existing) {
            Storage::disk(self::DISK)->delete($existing->file_path);
            $existing->delete();
        }

        $filename = Str::uuid() . '.pdf';
        $path     = $file->storeAs(self::DIR, $filename, self::DISK);

        return Payslip::create([
            'employee_id'  => $employee->id,
            'uploaded_by'  => $userId,
            'period_year'  => $data['period_year'],
            'period_month' => $data['period_month'],
            'file_path'    => $path,
            'file_name'    => $file->getClientOriginalName(),
            'gross_amount' => $data['gross_amount'] ?? null,
            'net_amount'   => $data['net_amount']   ?? null,
            'notes'        => $data['notes']        ?? null,
        ]);
    }

    public function delete(Payslip $payslip): void
    {
        Storage::disk(self::DISK)->delete($payslip->file_path);
        $payslip->delete();
    }

    public function download(Payslip $payslip): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(
            Storage::disk(self::DISK)->exists($payslip->file_path),
            404, 'File not found.'
        );

        return Storage::disk(self::DISK)->download(
            $payslip->file_path,
            $payslip->file_name,
        );
    }
}
