<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Services\InvoicePdfService;

class PortalInvoiceController extends Controller
{
    public function index()
    {
        $customer = $this->customer();
        if (! $customer) return $this->noCustomer();

        $invoices = Invoice::where('customer_id', $customer->id)
            ->with('lines')
            ->latest()
            ->get()
            ->map(fn ($i) => [
                'id'            => $i->id,
                'reference'     => $i->reference,
                'total'         => $i->total,
                'balance_due'   => $i->balance_due,
                'paid_total'    => $i->paid_total,
                'status'        => $i->status,
                'due_date'      => $i->due_date?->format('d M Y'),
                'issue_date'    => $i->issue_date?->format('d M Y'),
                'payment_token' => $i->payment_token,
                'payment_url'   => $i->payment_url,
            ]);

        return inertia('Portal/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Invoice $invoice)
    {
        $customer = $this->customer();
        abort_if(
            ! $customer || $invoice->customer_id !== $customer->id,
            403
        );

        $invoice->load(['lines', 'payments']);

        // Refresh payment token if expired or not set
        if ($invoice->balance_due > 0 && ! $invoice->isPaymentTokenValid()) {
            $invoice->generatePaymentToken();
            $invoice->refresh();
        }

        return inertia('Portal/Invoices/Show', [
            'invoice' => [
                'id'            => $invoice->id,
                'reference'     => $invoice->reference,
                'status'        => $invoice->status,
                'total'         => $invoice->total,
                'subtotal'      => $invoice->subtotal,
                'tax_total'     => $invoice->tax_total,
                'balance_due'   => $invoice->balance_due,
                'paid_total'    => $invoice->paid_total,
                'issue_date'    => $invoice->issue_date?->format('d M Y'),
                'due_date'      => $invoice->due_date?->format('d M Y'),
                'currency'      => $invoice->currency,
                'notes'         => $invoice->notes,
                'lines'         => $invoice->lines,
                'payments'      => $invoice->payments->map(fn ($p) => [
                    'amount'    => $p->amount,
                    'method'    => $p->method,
                    'paid_at'   => $p->paid_at?->format('d M Y'),
                    'reference' => $p->reference,
                ]),
                'payment_url'      => $invoice->payment_url,
                'deposit_required' => $invoice->deposit_required,
                'deposit_amount'   => $invoice->deposit_amount,
                'deposit_paid_at'  => $invoice->deposit_paid_at?->format('d M Y'),
                'amount_due_now'   => $invoice->amountDueNow(),
                'payment_stage'    => $invoice->paymentStageLabel(),
            ],
        ]);
    }

    public function downloadPdf(Invoice $invoice, InvoicePdfService $pdfService)
    {
        $customer = $this->customer();
        abort_if(
            ! $customer || $invoice->customer_id !== $customer->id,
            403
        );

        $pdf      = $pdfService->generate($invoice, withStamp: true);
        $filename = $pdfService->filename($invoice, withStamp: true);

        return $pdf->download($filename);
    }

    private function customer(): ?Customer
    {
        $user = Auth::guard('customer')->user();
        return Customer::where('user_id', $user->id)->first();
    }

    private function noCustomer()
    {
        return redirect()->route('portal.dashboard');
    }
}
