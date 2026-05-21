<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\Settings;
use App\Jobs\SendReceiptJob;
use App\PaymentGateways\GatewayManager;
use Illuminate\Http\Request;
use Modules\Financial\app\Models\Invoice;
use Modules\Financial\app\Services\InvoiceService;

class PaymentController extends Controller
{
    public function __construct(
        private GatewayManager $gatewayManager,
        private InvoiceService  $invoiceService,
    ) {}

    public function show(string $token)
    {
        $invoice = $this->findByToken($token);
        $gateway = $this->gatewayManager;
        $payment = Settings::group('payments');

        return inertia('Payment/Show', [
            'invoice' => $this->formatInvoice($invoice),
            'gateway' => [
                'name'       => $gateway->gatewayName(),
                'configured' => $gateway->isConfigured(),
                'test_mode'  => $gateway->isTestMode(),
            ],
            'bank' => [
                'account_name'    => $payment->get('bank_account_name'),
                'bank_name'       => $payment->get('bank_name'),
                'account_number'  => $payment->get('bank_account_number'),
                'branch_code'     => $payment->get('bank_branch_code'),
                'reference'       => ($payment->get('bank_reference_prefix') ?? 'INV-') . $invoice->reference,
                'instructions'    => $payment->get('payment_instructions'),
            ],
            'app' => [
                'name'     => Settings::group('general')->get('app_name', config('app.name')),
                'logo_url' => Settings::group('general')->get('logo_url'),
            ],
        ]);
    }

    public function initiate(Request $request, string $token)
    {
        $invoice = $this->findByToken($token);
        $gateway = $this->gatewayManager->active();

        if (! $gateway) {
            return back()->with('error', 'Online payments are not configured.');
        }

        // Backend determines what to charge — no client input needed
        $amount = $invoice->amountDueNow();

        if ($amount <= 0) {
            return redirect(route('pay.show', $token));
        }

        $returnUrl = route('pay.return', $token);
        $cancelUrl = route('pay.cancel', $token);
        $notifyUrl = route('webhooks.' . $this->gatewayManager->gatewayName());

        $redirectUrl = $gateway->initiatePayment(
            $invoice, $amount, $returnUrl, $cancelUrl, $notifyUrl
        );

        return redirect($redirectUrl);
    }

    public function handleReturn(Request $request, string $token)
    {
        $invoice = $this->findByToken($token);

        if ($invoice->status === 'paid') {
            return inertia('Payment/Success', [
                'invoice' => $this->formatInvoice($invoice),
                'app'     => [
                    'name'     => Settings::group('general')->get('app_name', config('app.name')),
                    'logo_url' => Settings::group('general')->get('logo_url'),
                ],
            ]);
        }

        if ($invoice->status === 'cancelled') {
            return inertia('Payment/Cancelled', [
                'invoice' => $this->formatInvoice($invoice),
                'app'     => [
                    'name'     => Settings::group('general')->get('app_name', config('app.name')),
                    'logo_url' => Settings::group('general')->get('logo_url'),
                ],
            ]);
        }

        // ITN hasn't arrived yet — show processing page
        return inertia('Payment/Processing', [
            'invoice' => $this->formatInvoice($invoice),
            'app'     => [
                'name'     => Settings::group('general')->get('app_name', config('app.name')),
                'logo_url' => Settings::group('general')->get('logo_url'),
            ],
        ]);
    }

    public function handleCancel(string $token)
    {
        $invoice = Invoice::with(['customer', 'lines'])
            ->where('payment_token', $token)
            ->firstOrFail();

        return inertia('Payment/Cancelled', [
            'invoice' => $this->formatInvoice($invoice),
            'app'     => [
                'name'     => Settings::group('general')->get('app_name', config('app.name')),
                'logo_url' => Settings::group('general')->get('logo_url'),
            ],
        ]);
    }

    private function findByToken(string $token): Invoice
    {
        $invoice = Invoice::with(['customer', 'lines'])
            ->where('payment_token', $token)
            ->first();

        abort_if(! $invoice, 404, 'Payment link not found.');
        abort_if(! $invoice->isPaymentTokenValid(), 410, 'This payment link has expired.');

        return $invoice;
    }

    private function formatInvoice(Invoice $invoice): array
    {
        return [
            'id'                 => $invoice->id,
            'reference'          => $invoice->reference,
            'status'             => $invoice->status,
            'customer_name'      => $invoice->customer->company_name,
            'customer_email'     => $invoice->customer->email,
            'lines'              => $invoice->lines->map(fn ($l) => [
                'description' => $l->description,
                'qty'         => $l->qty,
                'unit_price'  => $l->unit_price,
                'line_total'  => $l->line_total,
                'tax_rate'    => $l->tax_rate,
            ]),
            'subtotal'           => $invoice->subtotal,
            'tax_total'          => $invoice->tax_total,
            'total'              => $invoice->total,
            'paid_total'         => $invoice->paid_total,
            'balance_due'        => $invoice->balance_due,
            'deposit_required'   => $invoice->deposit_required,
            'deposit_percentage' => $invoice->deposit_percentage,
            'deposit_amount'     => $invoice->deposit_amount,
            'deposit_paid_at'    => $invoice->deposit_paid_at?->format('d M Y'),
            'issue_date'         => $invoice->issue_date?->format('d M Y'),
            'due_date'           => $invoice->due_date?->format('d M Y'),
            'currency'           => $invoice->currency,
            'token'              => $invoice->payment_token,
        ];
    }
}
