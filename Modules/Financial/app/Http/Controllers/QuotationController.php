<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Financial\app\Models\Customer;
use Modules\Financial\app\Models\Quotation;
use Modules\Financial\app\Models\TaxRate;
use Modules\Financial\app\Services\QuotationPdfService;
use Modules\Financial\app\Services\QuotationService;

class QuotationController extends Controller
{
    public function __construct(
        private QuotationService    $service,
        private QuotationPdfService $pdfService,
    ) {}

    public function index(Request $request)
    {
        $query = Quotation::with('customer')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', fn ($cq) =>
                      $cq->where('company_name', 'like', '%' . $request->search . '%')
                  );
            });
        }

        $quotations = $query->get()->map(fn ($q) => $this->formatQuotation($q));

        // Stats
        $stats = [
            'total'          => Quotation::count(),
            'pending_value'  => Quotation::where('status', 'sent')->sum('total'),
            'accepted_count' => Quotation::where('status', 'accepted')->count(),
            'declined_count' => Quotation::where('status', 'declined')->count(),
            'acceptance_rate'=> $this->acceptanceRate(),
        ];

        return Inertia::render('Financial/Pages/Quotations/Index', [
            'quotations' => $quotations,
            'stats'      => $stats,
            'filters'    => $request->only(['status', 'search']),
            'statuses'   => ['draft', 'sent', 'accepted', 'declined', 'expired', 'converted'],
        ]);
    }

    public function create()
    {
        return Inertia::render('Financial/Pages/Quotations/Create', [
            'customers' => Customer::active()->orderBy('company_name')->get(['id', 'company_name', 'email']),
            'taxRates'  => TaxRate::active()->orderBy('name')->get(['id', 'name', 'rate', 'is_default']),
            'products'  => \Modules\Financial\app\Models\Product::active()->orderBy('name')->get(['id', 'name', 'default_price', 'default_tax_rate', 'unit']),
            'netTerms'  => Quotation::NET_TERMS,
            'defaults'  => [
                'currency' => config('financial.currency', 'ZAR'),
                'tax_rate' => TaxRate::defaultRate()?->rate ?? 15,
                'valid_days' => 30,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateQuotation($request);
        $quotation = $this->service->create($validated, $request->user()->id);

        return redirect()
            ->route('financial.quotations.show', $quotation)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Quotation created',
                'message' => "{$quotation->reference} saved as draft.",
            ]);
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'lines', 'createdBy', 'convertedInvoice']);

        return Inertia::render('Financial/Pages/Quotations/Show', [
            'quotation' => $this->formatQuotation($quotation, detailed: true),
        ]);
    }

    public function edit(Quotation $quotation)
    {
        abort_if(
            ! in_array($quotation->status, ['draft']),
            403, 'Only draft quotations can be edited.'
        );

        $quotation->load('lines');

        return Inertia::render('Financial/Pages/Quotations/Create', [
            'quotation' => [
                'id'          => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'issue_date'  => $quotation->issue_date?->format('Y-m-d'),
                'valid_until' => $quotation->valid_until?->format('Y-m-d'),
                'net_terms'   => $quotation->net_terms,
                'notes'       => $quotation->notes,
                'terms'       => $quotation->terms,
                'lines'       => $quotation->lines->map(fn ($l) => [
                    'description' => $l->description,
                    'qty'         => $l->qty,
                    'unit_price'  => $l->unit_price,
                    'tax_rate'    => $l->tax_rate,
                ]),
            ],
            'customers' => Customer::active()->orderBy('company_name')->get(['id', 'company_name']),
            'taxRates'  => TaxRate::active()->get(['id', 'name', 'rate', 'is_default']),
            'products'  => \Modules\Financial\app\Models\Product::active()->get(['id', 'name', 'default_price', 'default_tax_rate', 'unit']),
            'netTerms'  => Quotation::NET_TERMS,
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        abort_if(
            ! in_array($quotation->status, ['draft']),
            403, 'Only draft quotations can be edited.'
        );

        $validated = $this->validateQuotation($request);
        $this->service->update($quotation, $validated);

        return redirect()
            ->route('financial.quotations.show', $quotation)
            ->with('toast', ['type' => 'success', 'title' => 'Quotation updated']);
    }

    public function destroy(Quotation $quotation)
    {
        abort_if($quotation->status === 'converted', 403, 'Converted quotations cannot be deleted.');
        $quotation->delete();

        return redirect()
            ->route('financial.quotations.index')
            ->with('toast', ['type' => 'success', 'title' => 'Quotation deleted']);
    }

    public function send(Quotation $quotation)
    {
        $this->service->send($quotation);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Quotation sent',
            'message' => "Queued for delivery to {$quotation->customer->email}",
        ]);
    }

    public function accept(Quotation $quotation)
    {
        $this->service->accept($quotation);
        return back()->with('toast', ['type' => 'success', 'title' => 'Quotation accepted']);
    }

    public function decline(Quotation $quotation)
    {
        $this->service->decline($quotation);
        return back()->with('toast', ['type' => 'success', 'title' => 'Quotation declined']);
    }

    public function convert(Quotation $quotation)
    {
        $invoice = $this->service->convertToInvoice($quotation);

        return redirect()
            ->route('financial.invoices.show', $invoice)
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Converted to invoice',
                'message' => "Invoice {$invoice->reference} created from {$quotation->reference}.",
            ]);
    }

    public function downloadPdf(Quotation $quotation)
    {
        $pdf      = $this->pdfService->generate($quotation, withStamp: true);
        $filename = $this->pdfService->filename($quotation);
        return $pdf->download($filename);
    }

    // ── Public endpoints (no auth) ────────────────────────────

    public function publicShow(string $token)
    {
        $quotation = $this->findByToken($token);

        // Handle direct action from email links
        $action = request()->query('action');

        return inertia('Quote/Show', [
            'quotation' => $this->formatPublicQuotation($quotation),
            'action'    => $action, // pre-fill intent from email button
            'app'       => [
                'name'     => \App\Facades\Settings::group('general')->get('app_name', config('app.name')),
                'logo_url' => \App\Facades\Settings::group('general')->get('logo_url'),
            ],
        ]);
    }

    public function publicAccept(string $token)
    {
        $quotation = $this->findByToken($token);
        $this->service->accept($quotation);

        return redirect("/quote/{$token}?accepted=1");
    }

    public function publicDecline(string $token)
    {
        $quotation = $this->findByToken($token);
        $this->service->decline($quotation);

        return redirect("/quote/{$token}?declined=1");
    }

    // ── Private helpers ───────────────────────────────────────

    private function findByToken(string $token): Quotation
    {
        $quotation = Quotation::with(['customer', 'lines'])
            ->where('quote_token', $token)
            ->first();

        abort_if(! $quotation, 404, 'Quotation not found.');
        abort_if(! $quotation->isTokenValid(), 410, 'This quotation link has expired.');

        return $quotation;
    }

    private function validateQuotation(Request $request): array
    {
        return $request->validate([
            'customer_id'         => 'required|uuid|exists:fin_customers,id',
            'issue_date'          => 'required|date',
            'valid_until'         => 'required|date|after_or_equal:issue_date',
            'net_terms'           => 'nullable|string',
            'notes'               => 'nullable|string',
            'terms'               => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.qty'         => 'required|numeric|min:0.01',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.tax_rate'    => 'nullable|numeric|min:0|max:100',
        ]);
    }

    private function formatQuotation(Quotation $q, bool $detailed = false): array
    {
        $base = [
            'id'          => $q->id,
            'reference'   => $q->reference,
            'status'      => $q->status,
            'customer'    => $q->customer?->company_name,
            'customer_id' => $q->customer_id,
            'total'       => $q->total,
            'valid_until' => $q->valid_until?->format('d M Y'),
            'issue_date'  => $q->issue_date?->format('d M Y'),
            'sent_at'     => $q->sent_at?->format('d M Y'),
            'accepted_at' => $q->accepted_at?->format('d M Y'),
            'declined_at' => $q->declined_at?->format('d M Y'),
            'quote_url'   => $q->quote_url,
        ];

        if (! $detailed) return $base;

        return array_merge($base, [
            'customer_detail'   => $q->customer,
            'lines'             => $q->lines,
            'subtotal'          => $q->subtotal,
            'tax_total'         => $q->tax_total,
            'notes'             => $q->notes,
            'terms'             => $q->terms,
            'currency'          => $q->currency,
            'net_terms'         => $q->net_terms,
            'created_by'        => $q->createdBy?->name,
            'converted_invoice' => $q->convertedInvoice
                ? ['id' => $q->convertedInvoice->id, 'reference' => $q->convertedInvoice->reference]
                : null,
            'is_expired'        => $q->isExpired(),
            'valid_until_raw'   => $q->valid_until?->format('Y-m-d'),
            'issue_date_raw'    => $q->issue_date?->format('Y-m-d'),
        ]);
    }

    private function formatPublicQuotation(Quotation $q): array
    {
        return [
            'id'           => $q->id,
            'reference'    => $q->reference,
            'status'       => $q->status,
            'customer'     => $q->customer?->company_name,
            'contact_name' => $q->customer?->contact_name,
            'lines'        => $q->lines->map(fn ($l) => [
                'description' => $l->description,
                'qty'         => $l->qty,
                'unit_price'  => $l->unit_price,
                'line_total'  => $l->line_total,
                'tax_rate'    => $l->tax_rate,
            ]),
            'subtotal'     => $q->subtotal,
            'tax_total'    => $q->tax_total,
            'total'        => $q->total,
            'issue_date'   => $q->issue_date?->format('d M Y'),
            'valid_until'  => $q->valid_until?->format('d M Y'),
            'notes'        => $q->notes,
            'terms'        => $q->terms,
            'currency'     => $q->currency,
            'token'        => $q->quote_token,
            'is_expired'   => $q->isExpired(),
        ];
    }

    private function acceptanceRate(): float
    {
        $total    = Quotation::whereIn('status', ['accepted', 'declined'])->count();
        $accepted = Quotation::where('status', 'accepted')->count();
        return $total > 0 ? round($accepted / $total * 100, 1) : 0;
    }
}
