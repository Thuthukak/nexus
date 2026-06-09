<?php

declare(strict_types=1);

namespace Modules\LMS\app\Services;

use App\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\LMS\app\Models\Certificate;
use Modules\LMS\app\Models\Enrollment;

class CertificateService
{
    public function issue(Enrollment $enrollment): Certificate
    {
        if ($enrollment->certificate) {
            return $enrollment->certificate;
        }

        $certNumber = 'CERT-' . strtoupper(Str::random(8)) . '-' . date('Y');
        $pdf        = $this->generatePdf($enrollment, $certNumber);

        $filename  = 'certificate-' . $enrollment->id . '.pdf';
        $path      = 'private/lms/certificates/' . $filename;

        Storage::put($path, $pdf->output());

        return Certificate::create([
            'enrollment_id'      => $enrollment->id,
            'certificate_number' => $certNumber,
            'file_path'          => $path,
            'issued_at'          => now(),
        ]);
    }

    public function download(Certificate $certificate): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(Storage::exists($certificate->file_path), 404);
        return Storage::download($certificate->file_path, 'certificate.pdf');
    }

    private function generatePdf(Enrollment $enrollment, string $certNumber): \Barryvdh\DomPDF\PDF
    {
        $enrollment->load(['cohort.course', 'student']);
        $logoUrl     = $this->resolveLogoUrl();
        $primaryColor = Settings::group('theme')->get('primary', '#1E3A5F');
        $appName     = Settings::group('general')->get('app_name', config('app.name'));

        return Pdf::loadView('lms::pdf.certificate', [
            'enrollment'   => $enrollment,
            'certNumber'   => $certNumber,
            'logoUrl'      => $logoUrl,
            'primaryColor' => $primaryColor,
            'appName'      => $appName,
        ])->setPaper('A4', 'landscape');
    }

    private function resolveLogoUrl(): ?string
    {
        $path = Settings::group('general')->get('logo_path');
        if (! $path) return null;
        $abs = storage_path('app/public/' . $path);
        return file_exists($abs) ? $abs : null;
    }
}
