<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  body { font-family: Arial, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .header { background: #374151; padding: 24px 32px; }
  .header h1 { color: #fff; font-size: 16px; margin: 0; font-weight: 600; }
  .body { padding: 28px 32px; }
  .body p { line-height: 1.6; margin-bottom: 14px; font-size: 14px; }
  .detail-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 16px 0; }
  .detail-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
  .detail-table td:first-child { color: #6b7280; width: 140px; }
  .detail-table td:last-child { font-weight: 600; }
  .footer { padding: 16px 32px; font-size: 11px; color: #9ca3af; text-align: center; border-top: 1px solid #f3f4f6; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{{ $appName }} — Invoice Sent Notification</h1>
  </div>
  <div class="body">
    <p>Invoice <strong>{{ $invoice->reference }}</strong> has been sent to the customer.</p>
    <table class="detail-table">
      <tr><td>Invoice</td><td>{{ $invoice->reference }}</td></tr>
      <tr><td>Customer</td><td>{{ $invoice->customer->company_name }}</td></tr>
      <tr><td>Sent To</td><td>{{ $invoice->customer->email }}</td></tr>
      <tr><td>Amount</td><td>{{ $currency }} {{ number_format($invoice->total, 2) }}</td></tr>
      <tr><td>Due Date</td><td>{{ $invoice->due_date->format('d F Y') }}</td></tr>
    </table>
    <p>This is an automated notification. No action required.</p>
  </div>
  <div class="footer">{{ $appName }} internal notification system</div>
</div>
</body>
</html>
