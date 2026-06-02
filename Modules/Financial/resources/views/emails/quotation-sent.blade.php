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
  .quote-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px 24px; margin: 20px 0; }
  .quote-box table { width: 100%; border-collapse: collapse; }
  .quote-box td { padding: 6px 0; font-size: 14px; }
  .quote-box td:first-child { color: #6b7280; width: 160px; }
  .quote-box td:last-child { font-weight: 600; color: #111827; }
  .total-row td { font-size: 16px; color: #1E3A5F; font-weight: 700; border-top: 1px solid #e5e7eb; padding-top: 10px; }
  .valid-until { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin: 16px 0; font-size: 13px; color: #92400e; }
  .cta-section { text-align: center; margin: 28px 0; }
  .btn-accept { display: inline-block; background: #059669; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 14px; margin: 0 6px; }
  .btn-decline { display: inline-block; background: #f9fafb; color: #6b7280; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 14px; border: 1px solid #e5e7eb; margin: 0 6px; }
  .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header"><h1>{{ $appName }}</h1></div>
  <div class="body">
    <p>Dear {{ $quotation->customer->contact_name ?? $quotation->customer->company_name }},</p>
    <p>Please find your quotation attached. Here is a summary:</p>

    <div class="quote-box">
      <table>
        <tr><td>Quote Number</td><td>{{ $quotation->reference }}</td></tr>
        <tr><td>Issue Date</td><td>{{ $quotation->issue_date->format('d F Y') }}</td></tr>
        <tr><td>Valid Until</td><td>{{ $quotation->valid_until->format('d F Y') }}</td></tr>
        <tr><td>Subtotal</td><td>{{ $currency }} {{ number_format($quotation->subtotal, 2) }}</td></tr>
        <tr><td>Tax</td><td>{{ $currency }} {{ number_format($quotation->tax_total, 2) }}</td></tr>
        <tr class="total-row"><td>Total</td><td>{{ $currency }} {{ number_format($quotation->total, 2) }}</td></tr>
      </table>
    </div>

    <div class="valid-until">
      ⏰ This quotation is valid until <strong>{{ $quotation->valid_until->format('d F Y') }}</strong>.
      Please respond before this date.
    </div>

    @if($quoteUrl)
    <div class="cta-section">
      <p style="margin-bottom: 16px; font-weight: 600;">Would you like to proceed?</p>
      <a href="{{ $quoteUrl }}?action=accept" class="btn-accept">✓ Accept Quotation</a>
      <a href="{{ $quoteUrl }}?action=decline" class="btn-decline">✗ Decline</a>
    </div>
    <p style="font-size: 12px; color: #9ca3af; text-align: center;">
      Or view the full quotation at: <a href="{{ $quoteUrl }}" style="color: #1E3A5F;">{{ $quoteUrl }}</a>
    </p>
    @endif

    @if($quotation->notes)
    <p><strong>Notes:</strong><br/>{{ $quotation->notes }}</p>
    @endif

    <p>If you have any questions, please don't hesitate to contact us.</p>
    <p>Thank you for considering our services.</p>
  </div>
  <div class="footer">
    <p>{{ $appName }} &mdash; This is an automated email.</p>
  </div>
</div>
</body>
</html>
