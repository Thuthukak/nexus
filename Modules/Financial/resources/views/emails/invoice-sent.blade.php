<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  body { font-family: Arial, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
  .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .header { background: #1E3A5F; padding: 28px 32px; }
  .header h1 { color: #fff; font-size: 20px; margin: 0; }
  .body { padding: 32px; }
  .body p { line-height: 1.6; margin-bottom: 16px; font-size: 14px; }
  .invoice-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px 24px; margin: 20px 0; }
  .invoice-box table { width: 100%; border-collapse: collapse; }
  .invoice-box td { padding: 6px 0; font-size: 14px; }
  .invoice-box td:first-child { color: #6b7280; width: 160px; }
  .invoice-box td:last-child { font-weight: 600; color: #111827; }
  .total-row td { font-size: 16px; color: #1E3A5F; font-weight: 700; border-top: 1px solid #e5e7eb; padding-top: 10px; }
  .pay-btn { display: block; background: #1E3A5F; color: #fff; text-decoration: none; text-align: center; padding: 14px 24px; border-radius: 8px; font-weight: 700; font-size: 15px; margin: 20px 0; }
  .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin: 24px 0 10px; }
  .bank-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .bank-table td { padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
  .bank-table td:first-child { color: #6b7280; width: 160px; }
  .bank-table td:last-child { font-weight: 600; color: #111827; }
  .ref-highlight { font-size: 16px; font-weight: 700; color: #1E3A5F; letter-spacing: 1px; }
  .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{{ $appName }}</h1>
  </div>
  <div class="body">
    <p>Dear {{ $invoice->customer->contact_name ?? $invoice->customer->company_name }},</p>
    <p>Please find your invoice attached. Here is a summary:</p>

    <div class="invoice-box">
      <table>
        <tr><td>Invoice Number</td><td>{{ $invoice->reference }}</td></tr>
        <tr><td>Issue Date</td><td>{{ $invoice->issue_date->format('d F Y') }}</td></tr>
        <tr><td>Due Date</td><td>{{ $invoice->due_date->format('d F Y') }}</td></tr>
        <tr><td>Subtotal</td><td>{{ $currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Tax</td><td>{{ $currency }} {{ number_format($invoice->tax_total, 2) }}</td></tr>
        @if($invoice->deposit_required)
        <tr><td>Deposit Required</td><td>{{ $currency }} {{ number_format($invoice->deposit_amount, 2) }} ({{ $invoice->deposit_percentage }}%)</td></tr>
        @endif
        <tr class="total-row"><td>Total Due</td><td>{{ $currency }} {{ number_format($invoice->total, 2) }}</td></tr>
      </table>
    </div>

    {{-- Online payment link --}}
    @if($paymentUrl)
    <div class="section-label">Pay Online</div>
    <a href="{{ $paymentUrl }}" class="pay-btn">
      Pay {{ $currency }} {{ number_format($invoice->balance_due, 2) }} Online →
    </a>
    @if($invoice->deposit_required && !$invoice->deposit_paid_at)
    <p style="font-size: 13px; color: #6b7280; text-align: center; margin-top: -10px;">
      Or pay the {{ $invoice->deposit_percentage }}% deposit of
      {{ $currency }} {{ number_format($invoice->deposit_amount, 2) }} at the same link.
    </p>
    @endif
    @endif

    {{-- Bank details --}}
    @if($bankDetails['account_number'] ?? false)
    <div class="section-label">Pay via EFT / Bank Transfer</div>
    <table class="bank-table">
      @if($bankDetails['account_name'] ?? false)
        <tr><td>Account Name</td><td>{{ $bankDetails['account_name'] }}</td></tr>
      @endif
      @if($bankDetails['bank_name'] ?? false)
        <tr><td>Bank</td><td>{{ $bankDetails['bank_name'] }}</td></tr>
      @endif
      <tr><td>Account Number</td><td>{{ $bankDetails['account_number'] }}</td></tr>
      @if($bankDetails['branch_code'] ?? false)
        <tr><td>Branch Code</td><td>{{ $bankDetails['branch_code'] }}</td></tr>
      @endif
      <tr>
        <td>Reference</td>
        <td><span class="ref-highlight">{{ $bankDetails['reference'] }}</span></td>
      </tr>
    </table>
    @if($bankDetails['instructions'] ?? false)
      <p style="font-size: 13px; color: #6b7280; margin-top: 12px;">{{ $bankDetails['instructions'] }}</p>
    @endif
    @endif

    <p style="margin-top: 24px;">
      Please ensure payment is made by <strong>{{ $invoice->due_date->format('d F Y') }}</strong>.
    </p>
    <p>If you have any questions, please don't hesitate to contact us.</p>
    <p>Thank you for your business.</p>
  </div>
  <div class="footer">
    <p>{{ $appName }} &mdash; This is an automated email. Please do not reply directly.</p>
  </div>
</div>
</body>
</html>
