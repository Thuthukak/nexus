<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\Quotation;

class PortalDashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $this->resolveCustomer();

        if (! $customer) {
            return inertia('Portal/NoCustomer');
        }

        // Financial stats
        $invoices = Invoice::where('customer_id', $customer->id)
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->get();

        $quotations = Quotation::where('customer_id', $customer->id)
            ->whereNotIn('status', ['expired'])
            ->latest()
            ->limit(5)
            ->get();

        $overdueInvoices = $invoices->filter(fn ($i) =>
            in_array($i->status, ['overdue'])
        );

        $stats = [
            'outstanding'    => $invoices
                ->whereIn('status', ['sent', 'approved', 'part_paid', 'overdue', 'deposit_paid'])
                ->sum('balance_due'),
            'overdue_count'  => $overdueInvoices->count(),
            'overdue_amount' => $overdueInvoices->sum('balance_due'),
            'paid_total'     => $invoices->where('status', 'paid')->sum('total'),
            'pending_quotes' => Quotation::where('customer_id', $customer->id)
                ->where('status', 'sent')
                ->count(),
        ];

        // Bookings
        $bookings = [];
        if (class_exists(\Modules\Bookings\app\Models\Booking::class)) {
            $bookings = \Modules\Bookings\app\Models\Booking::where(
                'customer_email', $customer->email
            )
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'id'           => $b->id,
                'reference'    => $b->reference,
                'service'      => $b->service?->name,
                'start_at'     => $b->start_at?->format('d M Y H:i'),
                'status'       => $b->status,
            ]);
        }

        return inertia('Portal/Dashboard', [
            'customer'   => [
                'id'           => $customer->id,
                'company_name' => $customer->company_name,
                'contact_name' => $customer->contact_name,
            ],
            'stats'      => $stats,
            'recentInvoices' => $invoices->take(5)->map(fn ($i) => $this->formatInvoice($i)),
            'pendingQuotes'  => $quotations->where('status', 'sent')
                ->map(fn ($q) => $this->formatQuotation($q)),
            'upcomingBookings' => $bookings,
        ]);
    }

    private function resolveCustomer(): ?Customer
    {
        $user = Auth::guard('customer')->user();
        return Customer::where('user_id', $user->id)->first();
    }

    private function formatInvoice(Invoice $i): array
    {
        return [
            'id'          => $i->id,
            'reference'   => $i->reference,
            'total'       => $i->total,
            'balance_due' => $i->balance_due,
            'status'      => $i->status,
            'due_date'    => $i->due_date?->format('d M Y'),
            'payment_token' => $i->payment_token,
        ];
    }

    private function formatQuotation(Quotation $q): array
    {
        return [
            'id'          => $q->id,
            'reference'   => $q->reference,
            'total'       => $q->total,
            'status'      => $q->status,
            'valid_until' => $q->valid_until?->format('d M Y'),
            'quote_token' => $q->quote_token,
        ];
    }
}
