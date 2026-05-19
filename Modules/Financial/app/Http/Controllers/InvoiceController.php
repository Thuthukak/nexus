<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\TaxRate;
use Modules\Financial\app\Services\InvoiceService;
use App\Jobs\SendReceiptJob;
class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    public function index(Request $request)
    {
        $query = Invoice::with('customer')->orderByDesc('created_at');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by reference or customer name
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', fn ($cq) =>
                        $cq->where('company_name', 'like', '%' . $request->search . '%')
                );
            });
        }

        $invoices = $query->get()->map(fn ($inv) => [
            'id'        => $inv->id,
            'reference' => $inv->reference,
            'customer'  => $inv->customer?->company_name,
            'total'     => $inv->total,
            'paid_total'=> $inv->paid_total,
            'status'    => $inv->status,
            'due_date'  => $inv->due_date?->format('d M Y'),
            'issue_date'=> $inv->issue_date?->format('d M Y'),
        ]);

        return Inertia::render('Financial/Pages/Invoices/Index', [
            'invoices' => $invoices,
            'filters'  => $request->only(['status', 'search']),
            'statuses' => ['draft', 'approved', 'sent', 'part_paid', 'paid', 'overdue', 'cancelled'],
        ]);
    }

    public function create()
    {
        return Inertia::render('Financial/Pages/Invoices/Create', [
            'customers' => Customer::active()->orderBy('company_name')->get(['id', 'company_name', 'email', 'vat_number']),
            'taxRates'  => TaxRate::active()->orderBy('name')->get(['id', 'name', 'rate', 'is_default']),
            'products'  => \Modules\Financial\app\Models\Product::active()->orderBy('name')->get(['id', 'name', 'default_price', 'default_tax_rate', 'unit']),
            'defaults'  => [
                'currency'   => config('financial.currency', 'ZAR'),
                'due_days'   => 30,
                'tax_rate'   => TaxRate::defaultRate()?->rate ?? 15,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'         => 'required|uuid|exists:fin_customers,id',
            'issue_date'          => 'required|date',
            'due_date'            => 'required|date',
            'notes'               => 'nullable|string',
            'deposit_required'   => 'boolean',
            'deposit_percentage' => 'nullable|numeric|min:1|max:99',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.tax_rate'    => 'nullable|numeric|min:0|max:100',
        ]);

         // Compute deposit amount from percentage after invoice total is known
        if (! empty($validated['deposit_required'])) {
            $validated['deposit_amount'] = 0; // will be computed after lines are saved
        }

        $invoice = $this->service->create($validated, $request->user()->id);

        // Update deposit_amount now that we have the total
        if ($invoice->deposit_required) {
            $invoice->update([
                'deposit_amount' => $invoice->computeDepositAmount(),
            ]);
        }

        return redirect()
            ->route('financial.invoices.show', $invoice)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Invoice created',
                'message' => "{$invoice->reference} saved as draft.",
            ]);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'lines', 'payments', 'createdBy']);

        return Inertia::render('Financial/Pages/Invoices/Show', [
            'invoice' => $this->formatInvoice($invoice),
        ]);
    }

    public function edit(Invoice $invoice)
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled']),
            403,
            'This invoice cannot be edited.'
        );

        $invoice->load('lines');

        return Inertia::render('Financial/Pages/Invoices/Edit', [
            'invoice'   => [
                'id'          => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'issue_date'  => $invoice->issue_date?->format('Y-m-d'),
                'due_date'    => $invoice->due_date?->format('Y-m-d'),
                'notes'       => $invoice->notes,
                'lines'       => $invoice->lines->map(fn ($l) => [
                    'description' => $l->description,
                    'qty'         => $l->qty,
                    'unit_price'  => $l->unit_price,
                    'tax_rate'    => $l->tax_rate,
                ]),
            ],
            'customers' => Customer::active()->orderBy('company_name')->get(['id', 'company_name']),
            'taxRates'  => TaxRate::active()->get(['id', 'name', 'rate', 'is_default']),
            'products'  => \Modules\Financial\app\Models\Product::active()->orderBy('name')->get(['id', 'name', 'default_price', 'default_tax_rate', 'unit']),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_if(
            in_array($invoice->status, ['paid', 'cancelled']),
            403,
            'This invoice cannot be edited.'
        );

        $validated = $request->validate([
            'customer_id'         => 'required|uuid|exists:fin_customers,id',
            'issue_date'          => 'required|date',
            'due_date'            => 'required|date',
            'notes'               => 'nullable|string',
            'deposit_required'   => 'boolean',
            'deposit_percentage' => 'nullable|numeric|min:1|max:99',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.tax_rate'    => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice = $this->service->update($invoice, $validated);

        return redirect()
            ->route('financial.invoices.show', $invoice)
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Invoice updated',
            ]);
    }

    public function destroy(Invoice $invoice)
    {
        abort_if(
            $invoice->status === 'paid',
            403,
            'Paid invoices cannot be deleted.'
        );

        $invoice->delete();

        return redirect()
            ->route('financial.invoices.index')
            ->with('toast', [
                'type'  => 'success',
                'title' => 'Invoice deleted',
            ]);
    }

    // Status transitions
    public function approve(Invoice $invoice)
    {
        $this->service->approve($invoice);
        return back()->with('toast', ['type' => 'success', 'title' => 'Invoice approved']);
    }

    public function markSent(Invoice $invoice)
    {
        $this->service->markSent($invoice);
        return back()->with('toast', ['type' => 'success', 'title' => 'Invoice marked as sent']);
    }

    public function cancel(Invoice $invoice)
    {
        $this->service->cancel($invoice);
        return back()->with('toast', ['type' => 'success', 'title' => 'Invoice cancelled']);
    }

    public function duplicate(Invoice $invoice)
    {
        $new = $this->service->duplicate($invoice);
        return redirect()
            ->route('financial.invoices.show', $new)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Invoice duplicated',
                'message' => "{$new->reference} created as draft.",
            ]);
    }

    // Record a payment
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount'    => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'method'    => 'required|in:cash,bank_transfer,card,cheque,other',
            'reference' => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
            'paid_at'   => 'required|date',
        ]);

        $this->service->recordPayment($invoice, $validated);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Payment recorded',
            'message' => 'R ' . number_format($validated['amount'], 2) . ' payment recorded.',
        ]);
    }

    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id'          => $invoice->id,
            'reference'   => $invoice->reference,
            'status'      => $invoice->status,
            'customer'    => $invoice->customer,
            'lines'       => $invoice->lines,
            'payments'    => $invoice->payments->map(fn ($p) => [
                'id'        => $p->id,
                'amount'    => $p->amount,
                'method'    => $p->method,
                'reference' => $p->reference,
                'notes'     => $p->notes,
                'paid_at'   => $p->paid_at?->format('d M Y'),
            ]),
            'subtotal'    => $invoice->subtotal,
            'tax_total'   => $invoice->tax_total,
            'total'       => $invoice->total,
            'paid_total'  => $invoice->paid_total,
            'balance_due' => $invoice->balance_due,
            'issue_date'  => $invoice->issue_date?->format('d M Y'),
            'due_date'    => $invoice->due_date?->format('d M Y'),
            'issue_date_raw' => $invoice->issue_date?->format('Y-m-d'),
            'due_date_raw'   => $invoice->due_date?->format('Y-m-d'),
            'notes'       => $invoice->notes,
            'created_by'  => $invoice->createdBy?->name,
            'currency'    => $invoice->currency,
            'receipt_sent_at'    => $invoice->receipt_sent_at?->format('d M Y H:i'),
            'last_sent_at'       => $invoice->last_sent_at?->format('d M Y H:i'),
            'deposit_required'   => $invoice->deposit_required,
            'deposit_percentage' => $invoice->deposit_percentage,
            'deposit_amount'     => $invoice->deposit_amount,
            'deposit_paid_at'    => $invoice->deposit_paid_at?->format('d M Y H:i'),
        ];
    }

    public function send(Invoice $invoice)
    {
        $this->service->queueSend($invoice);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Invoice queued for delivery',
            'message' => "Invoice {$invoice->reference} will be emailed to {$invoice->customer->email} shortly.",
        ]);
    }

    public function sendReceipt(Invoice $invoice)
    {
        abort_if(
            ! in_array($invoice->status, ['paid', 'part_paid']),
            422,
            'Receipts can only be sent for paid or part-paid invoices.'
        );

        abort_if(
            ! $invoice->customer->email,
            422,
            'This customer has no email address.'
        );

        SendReceiptJob::dispatch($invoice->id);

        $invoice->update(['receipt_sent_at' => now()]);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Receipt queued',
            'message' => "Receipt will be emailed to {$invoice->customer->email} shortly.",
        ]);
    }

    public function downloadPdf(Invoice $invoice)
    {
        $pdfService = app(\Modules\Financial\app\Services\InvoicePdfService::class);
        $pdf        = $pdfService->generate($invoice);
        $filename   = $pdfService->filename($invoice);

        return $pdf->download($filename);
    }

    public function downloadReceipt(Invoice $invoice)
    {
        abort_if(
            ! in_array($invoice->status, ['paid', 'part_paid']),
            422,
            'Receipts are only available for paid invoices.'
        );

        $pdfService = app(\Modules\Financial\app\Services\InvoicePdfService::class);
        $pdf        = $pdfService->generate($invoice, withStamp: true);
        $filename   = $pdfService->filename($invoice, withStamp: true);

        return $pdf->download($filename);
    }
}