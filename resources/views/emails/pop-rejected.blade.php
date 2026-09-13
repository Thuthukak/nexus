<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
.box { background: #fff8f8; border: 1px solid #fcd5d5; border-radius: 8px; padding: 20px; margin: 20px 0; }
</style></head>
<body>
<p>Dear {{ $invoice->customer->company_name ?? $invoice->customer->contact_name }},</p>
<p>Thank you for submitting your proof of payment for invoice <strong>{{ $invoice->reference }}</strong>.</p>
<p>Unfortunately, we were unable to verify your payment at this time.</p>
@if($invoice->pop_rejection_reason)
<div class="box">
  <strong>Reason:</strong><br/>{{ $invoice->pop_rejection_reason }}
</div>
@endif
<p>Please re-upload a valid proof of payment or contact us if you believe this is an error.</p>
<p>
  <a href="{{ url('/pay/' . $invoice->payment_token) }}"
     style="display:inline-block;background:#1a1a18;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">
    Re-upload Proof of Payment
  </a>
</p>
<p>Thank you,<br/>{{ config('app.name') }}</p>
</body>
</html>
