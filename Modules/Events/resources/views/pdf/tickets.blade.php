<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 12px;
    color: #1a1a2e;
    background: #fff;
  }

  /* ── Page break utility ───────────────────────────── */
  .page-break {
    page-break-after: always;
    display: block;
  }

  /* ── Summary page ─────────────────────────────────── */
  .summary {
    padding: 32px 36px;
  }
  .summary-header {
    display: table;
    width: 100%;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 3px solid {{ $primaryColor }};
  }
  .summary-header-left  { display: table-cell; vertical-align: middle; }
  .summary-header-right { display: table-cell; vertical-align: middle; text-align: right; }
  .summary-header .logo { height: 48px; width: auto; }
  .summary-header h1    { font-size: 22px; font-weight: 700; color: {{ $primaryColor }}; }
  .summary-header .sub  { font-size: 13px; color: #6b7280; margin-top: 3px; }
  .summary-header .badge {
    display: inline-block;
    padding: 4px 14px;
    background: #d1fae5;
    color: #065f46;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .event-info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .event-info-table td { padding: 6px 0; font-size: 12px; }
  .event-info-table td:first-child { color: #6b7280; width: 120px; }
  .event-info-table td:last-child  { font-weight: 600; color: #111827; }

  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .items-table thead th {
    background: {{ $primaryColor }};
    color: #fff;
    padding: 9px 12px;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .items-table thead th:last-child { text-align: right; }
  .items-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; font-size: 12px; }
  .items-table tbody td:last-child  { text-align: right; font-weight: 600; }
  .items-table tfoot td {
    padding: 10px 12px;
    font-size: 14px;
    font-weight: 700;
    color: {{ $primaryColor }};
    border-top: 2px solid {{ $primaryColor }};
  }
  .items-table tfoot td:last-child { text-align: right; }

  .summary-notice {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 12px 16px;
    font-size: 11px;
    color: #1e40af;
    margin-top: 16px;
    line-height: 1.7;
  }

  /* ── Individual ticket page ───────────────────────── */
  /*
   * Each ticket sits in a fixed-height wrapper that exactly fills one A4 page.
   * A4 portrait = 210mm × 297mm. We use px at 96dpi: ~794px × 1122px.
   * With 24px padding each side: inner area ~746px wide × ~1074px tall.
   * We keep the card well within that to guarantee no overflow.
   */
  .ticket-wrapper {
    width: 100%;
    padding: 28px 32px;
  }

  .ticket-card {
    border: 2px solid {{ $primaryColor }};
    border-radius: 10px;
    overflow: hidden;
  }

  /* Event banner — capped height so everything else fits */
  .ticket-banner {
    width: 100%;
    height: 130px;
    object-fit: cover;
    display: block;
  }
  .ticket-banner-placeholder {
    width: 100%;
    height: 130px;
    background: {{ $primaryColor }};
    display: block;
    position: relative;
  }
  .ticket-banner-title {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    text-align: center;
    padding: 0 20px;
  }

  /* Header strip */
  .ticket-strip {
    background: {{ $primaryColor }};
    padding: 10px 18px;
    display: table;
    width: 100%;
  }
  .ticket-strip-left  { display: table-cell; vertical-align: middle; }
  .ticket-strip-right { display: table-cell; vertical-align: middle; text-align: right; }
  .ticket-strip .org  { color: #fff; font-weight: 700; font-size: 13px; }
  .ticket-strip .ref  { color: rgba(255,255,255,0.65); font-size: 10px; margin-top: 1px; }

  /* Main body */
  .ticket-body { padding: 16px 18px; }

  .event-name {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 6px;
    line-height: 1.2;
  }
  .event-meta-row {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 3px;
    line-height: 1.5;
  }
  .event-meta-row strong { color: #374151; }

  /* Dashed tear line */
  .tear-line {
    border: none;
    border-top: 2px dashed #d1d5db;
    margin: 14px 0;
    position: relative;
  }

  /* Holder + QR row */
  .holder-qr-row {
    display: table;
    width: 100%;
  }
  .holder-cell { display: table-cell; vertical-align: top; padding-right: 16px; }
  .qr-cell     { display: table-cell; vertical-align: top; width: 110px; text-align: center; }

  .holder-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 2px; }
  .holder-name  { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 2px; }
  .holder-email { font-size: 11px; color: #6b7280; margin-bottom: 10px; }

  .type-badge {
    display: inline-block;
    padding: 5px 14px;
    background: {{ $primaryColor }};
    color: #fff;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 10px;
  }

  .meta-row { font-size: 10px; color: #9ca3af; margin-bottom: 2px; }
  .meta-row strong { color: #374151; font-size: 11px; }

  .qr-image { width: 100px; height: 100px; display: block; margin: 0 auto 4px; }
  .qr-label { font-size: 8px; color: #9ca3af; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }

  /* Ticket number bar */
  .ticket-number-bar {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 8px 18px;
    display: table;
    width: 100%;
  }
  .tn-left  { display: table-cell; vertical-align: middle; }
  .tn-right { display: table-cell; vertical-align: middle; text-align: right; }
  .tn-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; }
  .tn-value { font-size: 14px; font-weight: 700; color: #111827; letter-spacing: 1px; }
  .valid-badge {
    display: inline-block;
    background: #d1fae5;
    color: #065f46;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Footer note */
  .ticket-footer {
    background: {{ $primaryColor }}10;
    border-top: 1px solid {{ $primaryColor }}20;
    padding: 6px 18px;
    font-size: 9px;
    color: #6b7280;
    text-align: center;
  }
</style>
</head>
<body>

{{-- ════════════════════════════════════════════════════ --}}
{{-- BOOKING CONFIRMATION SUMMARY PAGE                   --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="summary">

  <div class="summary-header">
    <div class="summary-header-left">
      @if($logoPath)
        <img src="{{ $logoPath }}" class="logo" />
      @else
        <h1>{{ $appName }}</h1>
      @endif
      <div class="sub">Booking Confirmation</div>
    </div>
    <div class="summary-header-right">
      <div class="badge">✓ Payment Confirmed</div>
    </div>
  </div>

  <p style="font-size:13px;color:#374151;margin-bottom:16px;line-height:1.6;">
    Dear <strong>{{ $order->customer_name }}</strong>,<br/>
    Your payment has been received and your tickets are confirmed.
    Please present the tickets on the following pages at the event entrance.
  </p>

  <table class="event-info-table">
    <tr><td>Event</td>      <td>{{ $order->event->title }}</td></tr>
    <tr><td>Date</td>       <td>{{ $order->event->starts_at->format('l, d F Y \a\t H:i') }}</td></tr>
    <tr><td>Venue</td>      <td>{{ $order->event->venue ?? 'TBA' }}{{ $order->event->venue_address ? ' — ' . $order->event->venue_address : '' }}</td></tr>
    <tr><td>Reference</td>  <td><strong>{{ $order->reference }}</strong></td></tr>
    <tr><td>Email</td>      <td>{{ $order->customer_email }}</td></tr>
    <tr><td>Paid</td>       <td>{{ $order->paid_at?->format('d M Y H:i') ?? now()->format('d M Y H:i') }}</td></tr>
  </table>

  <table class="items-table">
    <thead>
      <tr>
        <th>Ticket Type</th>
        <th>Quantity</th>
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
    <tfoot>
      <tr>
        <td colspan="3">Total Paid</td>
        <td>{{ $currency }} {{ number_format($order->total, 2) }}</td>
      </tr>
    </tfoot>
  </table>

  <div class="summary-notice">
    📎 <strong>{{ $order->tickets->count() }} ticket(s)</strong> follow on the next pages —
    one ticket per page. Each ticket must be presented separately at the entrance.<br/>
    🎫 Tickets are non-transferable and valid for one entry each.
  </div>

</div>

{{-- Force page break AFTER summary, BEFORE first ticket --}}
<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- INDIVIDUAL TICKETS — one per page                   --}}
{{-- ════════════════════════════════════════════════════ --}}
@foreach($order->tickets as $ticket)
<div class="ticket-wrapper">
  <div class="ticket-card">

    {{-- Banner --}}
    @php
      $bannerAbs = $order->event->banner_path
        ? storage_path('app/public/' . $order->event->banner_path)
        : null;
    @endphp
    @if($bannerAbs && file_exists($bannerAbs))
      <img src="{{ $bannerAbs }}" class="ticket-banner" />
    @else
      <div class="ticket-banner-placeholder">
        <div class="ticket-banner-title">{{ $order->event->title }}</div>
      </div>
    @endif

    {{-- Strip --}}
    <div class="ticket-strip">
      <div class="ticket-strip-left">
        <div class="org">{{ $appName }}</div>
        <div class="ref">{{ $order->reference }}</div>
      </div>
      <div class="ticket-strip-right">
        <div style="color:#fff;font-size:10px;opacity:0.6;">TICKET</div>
        <div style="color:#fff;font-size:11px;font-weight:700;">
          {{ $loop->iteration }} of {{ $order->tickets->count() }}
        </div>
      </div>
    </div>

    {{-- Body --}}
    <div class="ticket-body">

      <div class="event-name">{{ $order->event->title }}</div>

      <div class="event-meta-row">
        <strong>📅</strong>
        {{ $order->event->starts_at->format('l, d F Y') }}
        at {{ $order->event->starts_at->format('H:i') }}
      </div>
      @if($order->event->venue)
      <div class="event-meta-row">
        <strong>📍</strong>
        {{ $order->event->venue }}{{ $order->event->venue_address ? ' — ' . $order->event->venue_address : '' }}
      </div>
      @endif

      <hr class="tear-line" />

      {{-- Holder info + QR --}}
      <div class="holder-qr-row">
        <div class="holder-cell">
          <div class="holder-label">Ticket Holder</div>
          <div class="holder-name">{{ $ticket->holder_name }}</div>
          <div class="holder-email">{{ $ticket->holder_email }}</div>

          <div class="type-badge">
            {{ $ticket->orderItem->ticketType->name ?? $ticket->orderItem->ticket_type_name }}
          </div>

          <div class="meta-row">
            Price: <strong>{{ $currency }} {{ number_format($ticket->orderItem->unit_price, 2) }}</strong>
          </div>
          <div class="meta-row">
            Order Ref: <strong>{{ $order->reference }}</strong>
          </div>
          <div class="meta-row">
            Issued: <strong>{{ now()->format('d M Y') }}</strong>
          </div>
        </div>

        <div class="qr-cell">
          {{-- Actual QR code as inline SVG/base64 --}}
          @if(isset($qrCodes[$ticket->id]))
            <img src="{{ $qrCodes[$ticket->id] }}" class="qr-image" />
          @else
            {{-- Fallback if QR generation fails --}}
            <div style="width:100px;height:100px;background:#f3f4f6;border:1px solid #e5e7eb;
                        display:flex;align-items:center;justify-content:center;
                        font-size:8px;color:#9ca3af;text-align:center;padding:4px;
                        word-break:break-all;">
              {{ $ticket->qr_data }}
            </div>
          @endif
          <div class="qr-label">Scan to verify</div>
        </div>
      </div>

    </div>

    {{-- Ticket number bar --}}
    <div class="ticket-number-bar">
      <div class="tn-left">
        <div class="tn-label">Ticket Number</div>
        <div class="tn-value">{{ $ticket->ticket_number }}</div>
      </div>
      <div class="tn-right">
        <span class="valid-badge">✓ Valid</span>
      </div>
    </div>

    {{-- Footer note --}}
    <div class="ticket-footer">
      {{ $appName }} &mdash; {{ $order->event->title }} &mdash;
      This ticket is valid for one entry only. Non-transferable.
    </div>

  </div>
</div>

{{-- Page break after every ticket EXCEPT the last one --}}
@if(! $loop->last)
  <div class="page-break"></div>
@endif

@endforeach

</body>
</html>
