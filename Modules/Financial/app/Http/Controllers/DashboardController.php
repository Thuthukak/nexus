<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $now           = now();
        $startOfMonth  = $now->copy()->startOfMonth();
        $endOfMonth    = $now->copy()->endOfMonth();

        // Mark overdue invoices
        Invoice::where('status', 'sent')
               ->where('due_date', '<', today())
               ->update(['status' => 'overdue']);

        $stats = [
            'total_outstanding' => Invoice::whereIn('status', ['approved', 'sent', 'part_paid', 'overdue'])
                ->sum(\DB::raw('total - paid_total')),

            'paid_this_month'   => Payment::whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->sum('amount'),

            'overdue_count'     => Invoice::where('status', 'overdue')->count(),

            'overdue_amount'    => Invoice::where('status', 'overdue')
                ->sum(\DB::raw('total - paid_total')),

            'draft_count'       => Invoice::where('status', 'draft')->count(),
            'total_customers'   => Customer::count(),
        ];

        $recentInvoices = Invoice::with('customer')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($inv) => [
                'id'        => $inv->id,
                'reference' => $inv->reference,
                'customer'  => $inv->customer?->company_name,
                'total'     => $inv->total,
                'paid_total'=> $inv->paid_total,
                'status'    => $inv->status,
                'due_date'  => $inv->due_date?->format('d M Y'),
            ]);

        $recentPayments = Payment::with('invoice.customer')
            ->orderByDesc('paid_at')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'        => $p->id,
                'reference' => $p->invoice?->reference,
                'customer'  => $p->invoice?->customer?->company_name,
                'amount'    => $p->amount,
                'method'    => $p->method,
                'paid_at'   => $p->paid_at?->format('d M Y'),
            ]);

        return Inertia::render('Financial/Pages/Dashboard', [
            'stats'          => $stats,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
        ]);
    }
}
