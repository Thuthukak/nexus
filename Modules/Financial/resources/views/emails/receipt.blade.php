<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  body { font-family: Arial, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .header { background: #059669; padding: 28px 32px; }
  .header h1 { color: #fff; font-size: 20px; margin: 0 0 4px; }
  .header p  { color: rgba(255,255,255,0.8); font-size: 13px; margin: 0; }
  .body { padding: 32px; }
  .body p { line-height: 1.6; margin-bottom: 16px; font-size: 14px; }
  .check-circle { width: 56px; height: 56px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
  .summary-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px 24px; margin: 20px 0; }
  .summary-box table { width: 100%; border-collapse: collapse; }
  .summary-box td { padding: 5px 0; font-size: 14px; }
  .summary-box td:first-child { color: #6b7280; }
  .summary-box td:last-child { font-weight: 600; color: #111827; text-align: right; }
  .total-row td { font-size: 16px; color: #059669; font-weight: 700; border-top: 1px solid #bbf7d0; padding-top: 10px; }
  .paid-badge { display: inline-block; background: #d1fae5; color: #059669; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
  .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{{ $appName }}</h1>
    <p>Payment Receipt</p>
  </div>
  <div class="body">
    <p>Dear {{ $invoice->customer->contact_name ?? $invoice->customer->company_name }},</p>

    <p>
      Thank you — your payment has been received.
      Please find your receipt attached to this email.
    </p>

    <div class="summary-box">
      <table>
        <tr>
          <td>Invoice Number</td>
          <td>{{ $invoice->reference }}</td>
        </tr>
        <tr>
          <td>Status</td>
          <td><span class="paid-badge">{{ strtoupper(str_replace('_', ' ', $invoice->status)) }}</span></td>
        </tr>
        <tr>
          <td>Invoice Total</td>
          <td>{{ $currency }} {{ number_format($invoice->total, 2) }}</td>
        </tr>
        <tr>
          <td>Amount Paid</td>
          <td>{{ $currency }} {{ number_format($invoice->paid_total, 2) }}</td>
        </tr>
        @if($invoice->balance_due > 0)
        <tr>
          <td>Balance Remaining</td>
          <td style="color: #dc2626;">{{ $currency }} {{ number_format($invoice->balance_due, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
          <td>{{ $invoice->status === 'paid' ? 'Fully Paid' : 'Part Paid' }}</td>
          <td>{{ $currency }} {{ number_format($invoice->paid_total, 2) }}</td>
        </tr>
      </table>
    </div>

    @if($invoice->balance_due > 0)
    <p style="color: #d97706; font-size: 13px;">
      <strong>Note:</strong> A balance of
      {{ $currency }} {{ number_format($invoice->balance_due, 2) }} remains outstanding.
      Please arrange payment before the due date.
    </p>
    @else
    <p>This invoice is now fully settled. No further action is required on your part.</p>
    @endif

    <p>If you have any questions, please don't hesitate to contact us.</p>
    <p>Thank you for your business.</p>
  </div>
  <div class="footer">
    <p>{{ $appName }} &mdash; This is an automated receipt. Please retain for your records.</p>
  </div>
</div>
</body>
</html>
