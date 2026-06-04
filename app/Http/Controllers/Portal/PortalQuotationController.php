<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Models\Quotation;

class PortalQuotationController extends Controller
{
    public function index()
    {
        $customer = $this->customer();
        if (! $customer) return redirect()->route('portal.dashboard');

        $quotations = Quotation::where('customer_id', $customer->id)
            ->latest()
            ->get()
            ->map(fn ($q) => [
                'id'          => $q->id,
                'reference'   => $q->reference,
                'total'       => $q->total,
                'status'      => $q->status,
                'valid_until' => $q->valid_until?->format('d M Y'),
                'issue_date'  => $q->issue_date?->format('d M Y'),
                'quote_token' => $q->quote_token,
                'quote_url'   => $q->quote_url,
                'is_expired'  => $q->isExpired(),
            ]);

        return inertia('Portal/Quotations/Index', [
            'quotations' => $quotations,
        ]);
    }

    public function show(Quotation $quotation)
    {
        $customer = $this->customer();
        abort_if(
            ! $customer || $quotation->customer_id !== $customer->id,
            403
        );

        // Refresh token if needed so the accept/decline link works
        if ($quotation->status === 'sent' && ! $quotation->isTokenValid()) {
            $quotation->generateToken();
            $quotation->refresh();
        }

        $quotation->load('lines');

        return inertia('Portal/Quotations/Show', [
            'quotation' => [
                'id'          => $quotation->id,
                'reference'   => $quotation->reference,
                'status'      => $quotation->status,
                'total'       => $quotation->total,
                'subtotal'    => $quotation->subtotal,
                'tax_total'   => $quotation->tax_total,
                'issue_date'  => $quotation->issue_date?->format('d M Y'),
                'valid_until' => $quotation->valid_until?->format('d M Y'),
                'currency'    => $quotation->currency,
                'notes'       => $quotation->notes,
                'terms'       => $quotation->terms,
                'lines'       => $quotation->lines,
                'quote_url'   => $quotation->quote_url,
                'quote_token' => $quotation->quote_token,
                'is_expired'  => $quotation->isExpired(),
                'accepted_at' => $quotation->accepted_at?->format('d M Y'),
                'declined_at' => $quotation->declined_at?->format('d M Y'),
            ],
        ]);
    }

    private function customer(): ?Customer
    {
        $user = Auth::guard('customer')->user();
        return Customer::where('user_id', $user->id)->first();
    }
}
