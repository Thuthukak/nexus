<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Financial\app\Models\Customer;
use Modules\HR\app\Models\HrDocument;
use Modules\HR\app\Services\HrDocumentService;

class PortalDocumentController extends Controller
{
    public function index()
    {
        $customer = $this->customer();
        if (! $customer) return redirect()->route('portal.dashboard');

        $documents = HrDocument::where('customer_id', $customer->id)
            ->where('visibility', 'customer')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($d) => [
                'id'          => $d->id,
                'name'        => $d->name,
                'type'        => $d->type,
                'type_label'  => HrDocument::TYPES[$d->type] ?? $d->type,
                'file_name'   => $d->file_name,
                'file_size'   => $d->file_size_formatted,
                'expiry_date' => $d->expiry_date?->format('d M Y'),
                'is_expired'  => $d->is_expired,
                'notes'       => $d->notes,
                'created_at'  => $d->created_at->format('d M Y'),
            ]);

        return inertia('Portal/Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function download(HrDocument $document, HrDocumentService $service)
    {
        $customer = $this->customer();

        // Must be customer-visible AND linked to this customer
        abort_if(
            ! $customer
            || $document->customer_id !== $customer->id
            || $document->visibility !== 'customer',
            403
        );

        return $service->download($document);
    }

    private function customer(): ?Customer
    {
        $user = Auth::guard('customer')->user();
        return Customer::where('user_id', $user->id)->first();
    }
}
