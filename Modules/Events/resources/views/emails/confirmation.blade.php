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
  .ticket-summary { width: 100%; border-collapse: collapse; margin: 16px 0; }
  .ticket-summary th { background: #1E3A5F; color: #fff; padding: 8px 12px; text-align: left; font-size: 12px; }
  .ticket-summary td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
  .ticket-summary td:last-child { text-align: right; font-weight: 600; }
  .total-row td { font-size: 15px; font-weight: 700; color: #1E3A5F; border-top: 2px solid #e5e7eb; padding-top: 10px; }
  .notice { background: #fefce8; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; font-size: 12px; color: #92400e; margin: 16px 0; }
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
        📅 {{ $order->event->starts_at->format('l, d F Y') }}
        at {{ $order->event->starts_at->format('H:i') }}<br/>
        @if($order->event->venue)
          📍 {{ $order->event->venue }}
          @if($order->event->venue_address)
            &mdash; {{ $order->event->venue_address }}
          @endif
          <br/>
        @endif
        🎫 Order Reference: <strong>{{ $order->reference }}</strong>
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
      📎 Your tickets are attached as a PDF. Please print or save them to your device.
      Present each ticket at the entrance — one ticket per person.
    </div>

    <p>We look forward to seeing you at the event!</p>
  </div>
  <div class="footer">
    {{ $appName }} &mdash; This is an automated confirmation email.
  </div>
</div>
</body>
</html>
