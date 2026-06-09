<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #1a1a2e; background: #fff; }

  /* One ticket per page */
  .ticket-page { page-break-after: always; padding: 24px; width: 100%; min-height: 267mm; display: flex; flex-direction: column; }
  .ticket-page:last-child { page-break-after: auto; }

  .ticket-card {
    border: 2px solid {{ $primaryColor }};
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
  }

  /* Banner */
  .event-banner {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
  }
  .banner-placeholder {
    width: 100%;
    height: 120px;
    background: {{ $primaryColor }};
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .banner-placeholder .event-title-banner {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    text-align: center;
    padding: 0 20px;
  }

  /* Header strip */
  .ticket-header {
    background: {{ $primaryColor }};
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .ticket-header .org { color: #fff; font-weight: 700; font-size: 15px; }
  .ticket-header .ticket-num { color: rgba(255,255,255,0.7); font-size: 11px; }

  /* Body */
  .ticket-body { padding: 20px; background: #fff; }

  .event-name { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 4px; }
  .event-meta { font-size: 12px; color: #6b7280; margin-bottom: 16px; line-height: 1.8; }
  .event-meta strong { color: #374151; }

  /* Divider with circles */
  .divider {
    position: relative;
    border-top: 2px dashed #e5e7eb;
    margin: 16px -20px;
  }
  .divider::before {
    content: '';
    position: absolute;
    left: -10px; top: -10px;
    width: 18px; height: 18px;
    background: #f9fafb;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
  }
  .divider::after {
    content: '';
    position: absolute;
    right: -10px; top: -10px;
    width: 18px; height: 18px;
    background: #f9fafb;
    border-radius: 50%;
    border: 2px solid #e5e7eb;
  }

  /* Holder section */
  .holder-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
  }
  .holder-info { flex: 1; }
  .holder-info .label { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
  .holder-info .value { font-size: 14px; font-weight: 600; color: #111827; }
  .holder-info .sub   { font-size: 11px; color: #6b7280; margin-top: 2px; }

  .ticket-type-badge {
    background: {{ $primaryColor }};
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
  }

  /* QR section */
  .qr-section {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }
  .qr-box {
    width: 80px;
    height: 80px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    text-align: center;
    color: #6b7280;
    padding: 4px;
    word-break: break-all;
    flex-shrink: 0;
  }
  .ticket-details { flex: 1; font-size: 11px; color: #6b7280; line-height: 2; }
  .ticket-details .detail-label { color: #9ca3af; font-size: 10px; text-transform: uppercase; }

  /* Footer */
  .ticket-footer {
    background: #f9fafb;
    padding: 10px 20px;
    font-size: 10px;
    color: #9ca3af;
    text-align: center;
    border-top: 1px solid #f3f4f6;
  }

  /* Order summary page */
  .summary-page { padding: 32px; }
  .summary-header { margin-bottom: 24px; border-bottom: 2px solid {{ $primaryColor }}; padding-bottom: 16px; }
  .summary-header h1 { font-size: 22px; font-weight: 700; color: {{ $primaryColor }}; }
  .summary-header p  { font-size: 13px; color: #6b7280; margin-top: 4px; }
  .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .summary-table th { background: {{ $primaryColor }}; color: #fff; padding: 10px 14px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
  .summary-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; font-size: 12px; }
  .summary-table td:last-child { text-align: right; font-weight: 600; }
  .summary-total { text-align: right; font-size: 16px; font-weight: 700; color: {{ $primaryColor }}; margin-top: 12px; }
</style>
</head>
<body>

{{-- ── Order Summary Page ─────────────────────────────── --}}
<div class="summary-page">
  <div class="summary-header">
    @if($logoPath)
      <img src="{{ $logoPath }}" style="height:40px;margin-bottom:10px;" />
    @endif
    <h1>Booking Confirmation</h1>
    <p>Order {{ $order->reference }} · Thank you, {{ $order->customer_name }}!</p>
  </div>

  <p style="font-size:13px;color:#374151;margin-bottom:16px;">
    Your tickets for <strong>{{ $order->event->title }}</strong> have been confirmed.
    Each ticket is on the following pages — please present them at the entrance.
  </p>

  {{-- Event info --}}
  <table style="width:100%;margin-bottom:20px;font-size:12px;">
    <tr>
      <td style="color:#6b7280;padding:4px 0;width:130px;">Event</td>
      <td style="font-weight:600;color:#111827;">{{ $order->event->title }}</td>
    </tr>
    <tr>
      <td style="color:#6b7280;padding:4px 0;">Date & Time</td>
      <td style="font-weight:600;color:#111827;">{{ $order->event->starts_at->format('l, d F Y') }} at {{ $order->event->starts_at->format('H:i') }}</td>
    </tr>
    <tr>
      <td style="color:#6b7280;padding:4px 0;">Venue</td>
      <td style="font-weight:600;color:#111827;">{{ $order->event->venue ?? 'TBA' }}</td>
    </tr>
    <tr>
      <td style="color:#6b7280;padding:4px 0;">Order Reference</td>
      <td style="font-weight:600;color:#111827;">{{ $order->reference }}</td>
    </tr>
  </table>

  {{-- Line items --}}
  <table class="summary-table">
    <thead>
      <tr>
        <th>Ticket Type</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->items as $item)
      <tr>
        <td>{{ $item->ticket_type_name }}</td>
        <td>{{ $item->quantity }}</td>
        <td>{{ $currency }} {{ number_format($item->unit_price, 2) }}</td>
        <td>{{ $currency }} {{ number_format($item->subtotal, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="summary-total">
    Total Paid: {{ $currency }} {{ number_format($order->total, 2) }}
  </div>

  <p style="font-size:11px;color:#9ca3af;margin-top:24px;text-align:center;">
    {{ $appName }} &mdash; Generated {{ now()->format('d F Y') }}
  </p>
</div>

{{-- ── Individual Tickets ─────────────────────────────── --}}
@foreach($order->tickets as $ticket)
<div class="ticket-page">
  <div class="ticket-card">

    {{-- Banner --}}
    @if($order->event->banner_path && file_exists(storage_path('app/public/' . $order->event->banner_path)))
      <img src="{{ storage_path('app/public/' . $order->event->banner_path) }}"
           class="event-banner" />
    @else
      <div class="banner-placeholder">
        <div class="event-title-banner">{{ $order->event->title }}</div>
      </div>
    @endif

    {{-- Header strip --}}
    <div class="ticket-header">
      <span class="org">{{ $appName }}</span>
      <span class="ticket-num">{{ $ticket->ticket_number }}</span>
    </div>

    {{-- Body --}}
    <div class="ticket-body">
      <div class="event-name">{{ $order->event->title }}</div>
      <div class="event-meta">
        <strong>📅</strong> {{ $order->event->starts_at->format('l, d F Y') }}
        at {{ $order->event->starts_at->format('H:i') }}<br/>
        @if($order->event->venue)
          <strong>📍</strong> {{ $order->event->venue }}
          @if($order->event->venue_address)
            &mdash; {{ $order->event->venue_address }}
          @endif
          <br/>
        @endif
      </div>

      <div class="divider"></div>

      <div class="holder-section">
        <div class="holder-info">
          <div class="label">Ticket Holder</div>
          <div class="value">{{ $ticket->holder_name }}</div>
          <div class="sub">{{ $ticket->holder_email }}</div>
          <div class="sub" style="margin-top:8px;">
            <strong>Order:</strong> {{ $order->reference }}
          </div>
        </div>
        <div>
          <div class="ticket-type-badge">
            {{ $ticket->orderItem->ticketType->name ?? $ticket->orderItem->ticket_type_name }}
          </div>
          <div style="font-size:11px;color:#6b7280;text-align:center;margin-top:6px;">
            {{ $currency }} {{ number_format($ticket->orderItem->unit_price, 2) }}
          </div>
        </div>
      </div>

      <div class="qr-section">
        {{-- QR placeholder — in production replace with actual QR image --}}
        <div class="qr-box">
          <div>
            <strong style="font-size:9px;display:block;margin-bottom:3px;">SCAN TO VERIFY</strong>
            {{ $ticket->qr_data }}
          </div>
        </div>
        <div class="ticket-details">
          <div class="detail-label">Ticket Number</div>
          <div style="font-weight:700;font-size:13px;color:#111827;margin-bottom:6px;">
            {{ $ticket->ticket_number }}
          </div>
          <div class="detail-label">Status</div>
          <div style="color:#059669;font-weight:600;text-transform:uppercase;font-size:11px;">
            ✓ Valid
          </div>
        </div>
      </div>
    </div>

    <div class="ticket-footer">
      {{ $appName }} &mdash; {{ $order->event->title }} &mdash;
      This ticket is valid for one entry. Non-transferable.
    </div>
  </div>
</div>
@endforeach

</body>
</html>
