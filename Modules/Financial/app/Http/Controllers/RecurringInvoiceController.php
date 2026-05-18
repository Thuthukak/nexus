<?php

declare(strict_types=1);

namespace Modules\Financial\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Models\RecurringInvoice;
use Modules\Financial\app\Services\RecurringInvoiceService;

class RecurringInvoiceController extends Controller
{
    public function __construct(private RecurringInvoiceService $service) {}

    public function index()
    {
        $schedules = RecurringInvoice::with(['customer', 'sourceInvoice', 'createdBy'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($s) => $this->formatSchedule($s));

        return Inertia::render('Financial/Pages/Recurring/Index', [
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'frequency'       => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'interval'        => 'required|integer|min:1|max:12',
            'start_date'      => 'required|date|after_or_equal:today',
            'end_date'        => 'nullable|date|after:start_date',
            'max_occurrences' => 'nullable|integer|min:1|max:120',
            'due_days'        => 'required|integer|min:1|max:365',
            'auto_send'       => 'boolean',
            'notes'           => 'nullable|string|max:500',
        ]);

        $schedule = $this->service->createSchedule($invoice, $validated, $request->user()->id);

        return redirect()
            ->route('financial.recurring.index')
            ->with('toast', [
                'type'    => 'success',
                'title'   => 'Recurring schedule created',
                'message' => "{$schedule->frequencyLabel()} invoice scheduled starting {$schedule->next_run_date->format('d M Y')}.",
            ]);
    }

    public function pause(RecurringInvoice $recurringInvoice)
    {
        $this->service->pause($recurringInvoice);
        return back()->with('toast', ['type' => 'success', 'title' => 'Schedule paused']);
    }

    public function resume(RecurringInvoice $recurringInvoice)
    {
        $this->service->resume($recurringInvoice);
        return back()->with('toast', ['type' => 'success', 'title' => 'Schedule resumed']);
    }

    public function cancel(RecurringInvoice $recurringInvoice)
    {
        $this->service->cancel($recurringInvoice);
        return back()->with('toast', ['type' => 'success', 'title' => 'Schedule cancelled']);
    }

    public function destroy(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->delete();
        return back()->with('toast', ['type' => 'success', 'title' => 'Schedule deleted']);
    }

    private function formatSchedule(RecurringInvoice $s): array
    {
        return [
            'id'                 => $s->id,
            'source_reference'   => $s->sourceInvoice?->reference,
            'source_invoice_id'  => $s->source_invoice_id,
            'customer'           => $s->customer?->company_name,
            'customer_email'     => $s->customer?->email,
            'frequency_label'    => $s->frequencyLabel(),
            'frequency'          => $s->frequency,
            'interval'           => $s->interval,
            'start_date'         => $s->start_date?->format('d M Y'),
            'end_date'           => $s->end_date?->format('d M Y'),
            'next_run_date'      => $s->next_run_date?->format('d M Y'),
            'last_run_date'      => $s->last_run_date?->format('d M Y'),
            'occurrences_count'  => $s->occurrences_count,
            'max_occurrences'    => $s->max_occurrences,
            'auto_send'          => $s->auto_send,
            'due_days'           => $s->due_days,
            'status'             => $s->status,
            'notes'              => $s->notes,
            'created_by'         => $s->createdBy?->name,
        ];
    }
}
