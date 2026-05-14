<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Services\InvoiceService;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $service) {}

    public function index()
    {
        $invoices = Invoice::with('customer')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($inv) => [
                'id'        => $inv->id,
                'reference' => $inv->reference,
                'customer'  => $inv->customer?->company_name,
                'total'     => $inv->total,
                'status'    => $inv->status,
                'due_date'  => $inv->due_date?->format('d M Y'),
            ]);

        return Inertia::render('Financial/Pages/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function create()
    {
        return Inertia::render('Financial/Pages/Invoices/Create', [
            'customers' => Customer::active()->get(['id', 'company_name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'         => 'required|uuid|exists:fin_customers,id',
            'due_date'            => 'required|date',
            'notes'               => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.tax_rate'    => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice = $this->service->create($validated, $request->user()->id);

        return redirect()
            ->route('financial.invoices.show', $invoice)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Invoice created',
                'message' => "{$invoice->reference} has been created as a draft.",
            ]);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'lines', 'payments', 'createdBy']);

        return Inertia::render('Financial/Pages/Invoices/Show', [
            'invoice' => [
                'id'          => $invoice->id,
                'reference'   => $invoice->reference,
                'status'      => $invoice->status,
                'customer'    => $invoice->customer,
                'lines'       => $invoice->lines,
                'payments'    => $invoice->payments,
                'subtotal'    => $invoice->subtotal,
                'tax_total'   => $invoice->tax_total,
                'total'       => $invoice->total,
                'paid_total'  => $invoice->paid_total,
                'balance_due' => $invoice->balance_due,
                'issue_date'  => $invoice->issue_date?->format('d M Y'),
                'due_date'    => $invoice->due_date?->format('d M Y'),
                'notes'       => $invoice->notes,
                'created_by'  => $invoice->createdBy?->name,
            ],
        ]);
    }
}
