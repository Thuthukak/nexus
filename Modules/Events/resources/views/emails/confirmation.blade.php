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
  .body p { line-height: 1.7; font-size: 14px; margin-bottom: 14px; }
  .event-box { background: #f0f9ff; border-left: 4px solid #1E3A5F; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 20px 0; }
  .event-box .event-title { font-size: 17px; font-weight: 700; color: #1E3A5F; margin-bottom: 8px; }
  .event-box .meta { font-size: 13px; color: #374151; line-height: 2; }
  .meta-row { display: inline-flex; align-items: center; gap: 6px; }
  .ticket-summary { width: 100%; border-collapse: collapse; margin: 16px 0; }
  .ticket-summary th { background: #1E3A5F; color: #fff; padding: 8px 12px; text-align: left; font-size: 12px; }
  .ticket-summary td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
  .ticket-summary td:last-child { text-align: right; font-weight: 600; }
  .total-row td { font-size: 15px; font-weight: 700; color: #1E3A5F; border-top: 2px solid #e5e7eb; padding-top: 10px; }
  .notice { background: #fefce8; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; font-size: 12px; color: #92400e; margin: 16px 0; }
  .notice-inner { display: inline-flex; align-items: flex-start; gap: 8px; }
  .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <h1>{{ $appName }}</h1>
  </div>
  <div class="body">
    <p>Dear {{ $order->customer_name }},</p>
    <p>
      Your payment has been confirmed and your tickets are ready!
      Please find your ticket PDF attached to this email.
    </p>
    <div class="event-box">
      <div class="event-title">{{ $order->event->title }}</div>
      <div class="meta">

        <!-- Calendar icon -->
        <span class="meta-row">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1E3A5F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;vertical-align:middle;">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          {{ $order->event->starts_at->format('l, d F Y') }} at {{ $order->event->starts_at->format('H:i') }}
        </span><br/>

        @if($order->event->venue)
        <!-- Location pin icon -->
        <span class="meta-row">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1E3A5F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;vertical-align:middle;">
            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
          </svg>
          {{ $order->event->venue }}
          @if($order->event->venue_address)
            &mdash; {{ $order->event->venue_address }}
          @endif
        </span><br/>
        @endif

        <!-- Ticket icon -->
        <span class="meta-row">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1E3A5F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;vertical-align:middle;">
            <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
          </svg>
          Order Reference:  <strong>{{ $order->reference }}</strong>
        </span>

      </div>
    </div>
    <table class="ticket-summary">
      <thead>
        <tr>
          <th>Ticket Type</th>
          <th>Qty</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
        <tr>
          <td>{{ $item->ticket_type_name }}</td>
          <td>{{ $item->quantity }}</td>
          <td>{{ $currency }} {{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="2">Total Paid</td>
          <td>{{ $currency }} {{ number_format($order->total, 2) }}</td>
        </tr>
      </tbody>
    </table>
    <div class="notice">
      <span class="notice-inner">
        <!-- Paperclip icon -->
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;">
          <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66L9.41 17.41a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
        </svg>
        <span>Your tickets are attached as a PDF. Please print or save them to your device.
        Present each ticket at the entrance — one ticket per person.</span>
      </span>
    </div>
    <p>We look forward to seeing you at the event!</p>
  </div>
  <div class="footer">
    {{ $appName }} &mdash; This is an automated confirmation email.
  </div>
</div>
</body>
</html>