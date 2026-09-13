<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
  .card { background: #fff; border-radius: 10px; padding: 28px 32px; border: 1px solid #e5e5e5; }
  .ref  { background: #1a1a18; color: #fff; font-family: monospace; font-size: 18px; font-weight: bold;
          letter-spacing: 2px; padding: 12px 20px; border-radius: 8px; display: inline-block; margin: 12px 0; }
  .badge { display: inline-block; background: #fef9c3; color: #854d0e; font-size: 12px; font-weight: bold;
           padding: 4px 10px; border-radius: 20px; border: 1px solid #fde68a; }
  .footer { font-size: 12px; color: #999; margin-top: 24px; }
</style>
</head>
<body>
<div class="card">
  <p>Hi {{ $invoice->customer->company_name ?? $invoice->customer->contact_name }},</p>

  <p>
    Thank you — we've received your proof of payment for invoice
    <strong>{{ $invoice->reference }}</strong>.
  </p>

  <div class="ref">{{ $invoice->reference }}</div>

  <p style="margin-top:8px;">
    <span class="badge">⏳ Awaiting Verification</span>
  </p>

  <p>
    Our team will verify your payment and release your order confirmation within
    <strong>1 business day</strong>. We'll send you another email as soon as it's confirmed.
  </p>

  @if($invoice->pop_notes)
  <p style="color:#666;font-style:italic;border-left:3px solid #e5e5e5;padding-left:12px;">
    Your note: {{ $invoice->pop_notes }}
  </p>
  @endif

  <p>If you have any questions, please reply to this email and quote your reference number.</p>

  <p>Thank you,<br/><strong>{{ config('app.name') }}</strong></p>
</div>
<p class="footer">Invoice {{ $invoice->reference }} &middot; {{ config('app.name') }}</p>
</body>
</html>
