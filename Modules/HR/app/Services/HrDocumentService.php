<?php

declare(strict_types=1);

namespace Modules\HR\app\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\HR\app\Models\HrDocument;

class HrDocumentService
{
    private const DISK = 'local'; // storage/app — private
    private const DIR  = 'private/hr/documents';

    public function store(
        UploadedFile $file,
        array        $data,
        int          $userId,
    ): HrDocument {
        $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs(self::DIR, $filename, self::DISK);

        return HrDocument::create([
            'employee_id'   => $data['employee_id'] ?? null,
            'customer_id'   => $data['customer_id'] ?? null,
            'uploaded_by'   => $userId,
            'name'          => $data['name'],
            'type'          => $data['type'],
            'file_path'     => $path,
            'file_name'     => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'visibility'    => $data['visibility'] ?? 'web',
            'expiry_date'   => $data['expiry_date'] ?? null,
            'notes'         => $data['notes'] ?? null,
        ]);
    }

    public function delete(HrDocument $document): void
    {
        Storage::disk(self::DISK)->delete($document->file_path);
        $document->delete();
    }

    public function download(HrDocument $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(
            Storage::disk(self::DISK)->exists($document->file_path),
            404, 'File not found.'
        );

        return Storage::disk(self::DISK)->download(
            $document->file_path,
            $document->file_name,
        );
    }

    public function processExpiry(int $warningDays = 30): array
    {
        $stats = ['expired' => 0, 'expiring_soon' => 0];

        // Mark newly expired documents
        HrDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '<', today())
            ->where('is_expired', false)
            ->each(function (HrDocument $doc) use (&$stats) {
                $doc->update(['is_expired' => true]);
                $stats['expired']++;
            });

        // Find expiring soon — not yet notified
        HrDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays($warningDays))
            ->where('is_expired', false)
            ->where('expiry_notified', false)
            ->each(function (HrDocument $doc) use (&$stats) {
                $stats['expiring_soon']++;
            });

        return $stats;
    }
}
