<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  body { font-family: Arial, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .header { background: #1E3A5F; padding: 28px 32px; }
  .header h1 { color: #fff; font-size: 20px; margin: 0; }
  .body { padding: 32px; }
  .body p { line-height: 1.6; margin-bottom: 16px; }
  .invoice-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
  .invoice-box table { width: 100%; border-collapse: collapse; }
  .invoice-box td { padding: 5px 0; font-size: 14px; }
  .invoice-box td:first-child { color: #6b7280; width: 140px; }
  .invoice-box td:last-child { font-weight: 600; color: #111827; }
  .total-row td { font-size: 16px; color: #1E3A5F; font-weight: 700; border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 8px; }
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
    <p>Please find your invoice attached to this email. Here is a summary:</p>

    <div class="invoice-box">
      <table>
        <tr><td>Invoice Number</td><td>{{ $invoice->reference }}</td></tr>
        <tr><td>Issue Date</td><td>{{ $invoice->issue_date->format('d F Y') }}</td></tr>
        <tr><td>Due Date</td><td>{{ $invoice->due_date->format('d F Y') }}</td></tr>
        <tr><td>Subtotal</td><td>{{ $currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Tax</td><td>{{ $currency }} {{ number_format($invoice->tax_total, 2) }}</td></tr>
        <tr class="total-row"><td>Total Due</td><td>{{ $currency }} {{ number_format($invoice->total, 2) }}</td></tr>
      </table>
    </div>

    @if($invoice->notes)
    <p><strong>Notes:</strong><br/>{{ $invoice->notes }}</p>
    @endif

    <p>Please ensure payment is made by <strong>{{ $invoice->due_date->format('d F Y') }}</strong>.</p>
    <p>If you have any questions regarding this invoice, please don't hesitate to contact us.</p>
    <p>Thank you for your business.</p>
  </div>
  <div class="footer">
    <p>{{ $appName }} &mdash; This is an automated email, please do not reply directly to this address.</p>
  </div>
</div>
</body>
</html>
