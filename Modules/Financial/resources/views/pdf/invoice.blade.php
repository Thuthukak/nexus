<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Invoice {{ $invoice->reference }}</title>
<style>

  @font-face {
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 400;
    src: url("{{ storage_path('fonts/Roboto-Regular.ttf') }}") format('truetype');
  }
  @font-face {
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 500;
    src: url("{{ storage_path('fonts/Roboto-Medium.ttf') }}") format('truetype');
  }
  @font-face {
    font-family: 'Roboto';
    font-style: normal;
    font-weight: 700;
    src: url("{{ storage_path('fonts/Roboto-Bold.ttf') }}") format('truetype');
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Roboto', sans-serif;
    font-weight: 400;
    font-size: 12px;
    color: #1a1a1a;
    background: #fff;
  }

  .page { padding: 40px 48px; }

  /* ── Header ── */
  .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
  .header-table td { vertical-align: top; padding: 0; border: none; }

  .logo-img         { max-height: 60px; max-width: 160px; }
  .logo-placeholder { font-size: 20px; font-weight: 700; color: {{ $primaryColor }}; }

  .invoice-heading {
    font-size: 34px;
    font-weight: 700;
    color: #1a1a1a
    letter-spacing: 1px;
    line-height: 1.6;
    margin-bottom: 6px;
  }
  .mt-3 { margin-top: 12px; }

  .company-name-text {
    font-weight: 700;
    font-size: 12px;
    line-height: 1.4;
  }
  .reference-text {
    font-size: 11px;
    color: #333;
    line-height: 1.5;
  }

  .badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 6px;
  }

  .badge-draft     { background: #f3f4f6; color: #6b7280; }
  .badge-approved  { background: #fef3c7; color: #d97706; }
  .badge-sent      { background: #dbeafe; color: #2563eb; }
  .badge-paid      { background: #d1fae5; color: #059669; }
  .badge-overdue   { background: #fee2e2; color: #dc2626; }
  .badge-part_paid { background: #fef3c7; color: #d97706; }
  .badge-cancelled { background: #f3f4f6; color: #6b7280; }

  /* ── Divider ── */
  .divider { border: none; border-top: 1px solid #ccc; margin-bottom: 20px; }

  /* ── Bill To + Meta ── */
  .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  .meta-table td { vertical-align: top; padding: 0; border: none; }

  .bill-label {
    font-size: 10px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 3px;
    font-weight: 500;
  }
  .bill-name   { font-weight: 700; font-size: 13px; line-height: 1.3; }
  .bill-detail {
    font-size: 11px;
    color: #333;
    line-height: 1.5;
    margin-top: 2px;
  }

  .meta-right  { text-align: right; }
  .meta-row    { font-size: 12px; line-height: 1.6; }
  .meta-label  { color: #555; }

  .amount-due-box {
    display: inline-table;
    background: #f0f0f0;
    border-radius: 4px;
    margin-top: 6px;
  }
  .amount-due-inner  { display: table-cell; padding: 5px 14px; }
  .amount-due-label  { font-size: 11px; font-weight: 700; line-height: 1.3; }
  .amount-due-value  { font-size: 14px; font-weight: 700; line-height: 1.3; }

  /* ── Items table ── */
  .items-table { width: 100%; border-collapse: collapse; }

  .items-table thead tr { background: {{ $primaryColor }}; }
  .items-table thead th {
    padding: 9px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    text-align: left;
    letter-spacing: 0.2px;
  }
  .items-table thead th.text-right { text-align: right; }

  .items-table tbody td {
    padding: 10px 14px;
    font-size: 12px;
    color: #1a1a1a;
    border-bottom: 1px solid #eee;
    vertical-align: top;
  }
  .items-table tbody td.text-right { text-align: right; }
  .item-title { font-weight: 700; font-size: 12px; line-height: 1.3; }
  .item-sub   { font-size: 11px; color: #555; margin-top: 1px; line-height: 1.3; }

  /* ── Totals ── */
  .totals-outer { width: 100%; border-collapse: collapse; margin-top: 2px; }
  .totals-outer td { border: none; padding: 0; }
  .totals-inner { width: 260px; float: right; }

  .t-row {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 1px solid #eee;
  }
  .t-row td { padding: 6px 0; font-size: 12px; }
  .t-label { color: #555; text-align: right; padding-right: 18px; }
  .t-value { text-align: right; white-space: nowrap; }

  .t-row.grand-total { border-top: 1px solid #bbb; border-bottom: 1px solid #bbb; }
  .t-row.grand-total td { font-weight: 700; font-size: 13px; padding: 8px 0; color: {{ $primaryColor }}; }

  .t-row.paid-row td { color: #059669; }

  .t-row.amount-due { border-bottom: none; }
  .t-row.amount-due td { font-weight: 700; font-size: 13px; padding-top: 8px; color: #dc2626; }

  /* ── Notes ── */
  .notes-section { margin-top: 28px; clear: both; }
  .notes-label {
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 4px;
  }
  .notes-text {
    font-size: 11px;
    color: #333;
    line-height: 1.5;
  }

  /* ── Status stamp ── */
  .status-stamp {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-35deg);
    font-weight: 900;
    border-radius: 16px;
    letter-spacing: 6px;
    text-transform: uppercase;
    pointer-events: none;
    z-index: 1000;
    white-space: nowrap;
  }

  /* ── Footer ── */
  .footer {
    margin-top: 52px;
    text-align: center;
    font-size: 11px;
    color: #aaa;
  }

  .clearfix::after { content: ''; display: table; clear: both; }

</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <table class="header-table">
    <tr>
      <td style="width: 45%;">
        @if($logoUrl)
          <img src="{{ $logoUrl }}" class="logo-img" alt="{{ $appName }}">
        @else
          <div class="logo-placeholder">{{ $appName }}</div>
        @endif
      </td>
      <td style="text-align: right;">
        <div class="invoice-heading">INVOICE</div>
        <div class="company-name-text">{{ $appName }}</div>
        <div class="suburb-text">{{ $streetAddress }}</div>
        <div class="suburb-text">{{ $suburb }}</div>
        <div class="city-text">{{ $city }}, {{ $province }} {{ $postalCode }}</div>
        <div class="country-text">{{ $country }}</div>

        <div class="mt-3">
          @if($telephone) <div class="phone-text">Tel: {{ $telephone }}</div> @endif
          @if($mobile)    <div class="mobile-text">Mobile: {{ $mobile }}</div> @endif
          @if($website)   <div class="website-text">{{ $website }}</div> @endif
        </div>

      </td>
    </tr>
  </table>

  <hr class="divider">

  <!-- Bill To + Invoice Meta -->
  <table class="meta-table">
    <tr>
      <td style="width: 50%;">
        <div class="bill-label">Bill To</div>
        <div class="bill-name">{{ $invoice->customer->company_name }}</div>
        <div class="bill-detail">
          @if($invoice->customer->contact_name) {{ $invoice->customer->contact_name }}<br> @endif
          @if($invoice->customer->email)        {{ $invoice->customer->email }}<br> @endif
          @if($invoice->customer->vat_number)   VAT: {{ $invoice->customer->vat_number }}<br> @endif
          @if($invoice->customer->address)
            @php $addr = $invoice->customer->address; @endphp
            @if(!empty($addr['line1'])) {{ $addr['line1'] }}<br> @endif
            @if(!empty($addr['line2'])) {{ $addr['line2'] }}<br> @endif
            @if(!empty($addr['city'])) {{ $addr['city'] }}@if(!empty($addr['code'])), {{ $addr['code'] }}@endif @endif
          @endif
        </div>
      </td>
      <td style="text-align: right; vertical-align: top;">
        <div class="meta-right">
          <div class="meta-row"><span class="meta-label">Invoice Reference: </span>{{ $invoice->reference }}</div>
          <div class="meta-row"><span class="meta-label">Issue Date: </span>{{ $invoice->issue_date->format('F j, Y') }}</div>
          <div class="meta-row"><span class="meta-label">Payment Due: </span>{{ $invoice->due_date->format('F j, Y') }}</div>
          <div class="meta-row"><span class="meta-label">Currency: </span>{{ $invoice->currency }}</div>
        </div>
        <div style="text-align: right; margin-top: 8px;">
          <div class="amount-due-box">
            <div class="amount-due-inner">
              <div class="amount-due-label">Amount Due ({{ $invoice->currency }}):</div>
              <div class="amount-due-value">{{ $currency }} {{ number_format($invoice->balance_due, 2) }}</div>
            </div>
          </div>
        </div>
      </td>
    </tr>
  </table>

  <!-- Line Items -->
  <table class="items-table">
    <thead>
      <tr>
        <th style="width: 46%;">Description</th>
        <th class="text-right" style="width: 14%;">Qty</th>
        <th class="text-right" style="width: 20%;">Unit Price</th>
        <th class="text-right" style="width: 20%;">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->lines as $line)
      <tr>
        <td>
          <div class="item-title">{{ $line->description }}</div>
        </td>
        <td class="text-right">{{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }}</td>
        <td class="text-right">
          <div>{{ $currency }}{{ number_format($line->unit_price, 2) }}</div>
          <div class="item-sub">Tax: {{ $line->tax_rate }}%{{ $line->is_tax_inclusive ? ' incl.' : ' excl.' }}</div>
        </td>
        <td class="text-right">{{ $currency }}{{ number_format($line->line_total, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Totals -->
  <div class="clearfix">
    <div class="totals-inner">

      @php
        $allInclusive = $invoice->lines->isNotEmpty()
            && $invoice->lines->every(fn($l) => $l->is_tax_inclusive);
        $allExclusive = $invoice->lines->every(fn($l) => ! $l->is_tax_inclusive);
      @endphp

      @if($allInclusive)
        {{-- SA standard: price includes VAT, show total first then VAT breakdown --}}
        <table class="t-row grand-total"><tr>
          <td class="t-label">Total (incl. VAT):</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->total, 2) }}</td>
        </tr></table>
        <table class="t-row"><tr>
          <td class="t-label">VAT {{ $invoice->lines->first()?->tax_rate ?? 0 }}% (incl.):</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->tax_total, 2) }}</td>
        </tr></table>
        <table class="t-row"><tr>
          <td class="t-label">Net (excl. VAT):</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->total - $invoice->tax_total, 2) }}</td>
        </tr></table>
      @elseif($allExclusive)
        {{-- Exclusive: net + tax = total --}}
        <table class="t-row"><tr>
          <td class="t-label">Subtotal (excl. VAT):</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
        </tr></table>
        <table class="t-row"><tr>
          <td class="t-label">VAT:</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->tax_total, 2) }}</td>
        </tr></table>
        <table class="t-row grand-total"><tr>
          <td class="t-label">Total:</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->total, 2) }}</td>
        </tr></table>
      @else
        {{-- Mixed: show all three rows --}}
        <table class="t-row"><tr>
          <td class="t-label">Subtotal:</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->subtotal, 2) }}</td>
        </tr></table>
        <table class="t-row"><tr>
          <td class="t-label">Tax:</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->tax_total, 2) }}</td>
        </tr></table>
        <table class="t-row grand-total"><tr>
          <td class="t-label">Total:</td>
          <td class="t-value">{{ $currency }}{{ number_format($invoice->total, 2) }}</td>
        </tr></table>
      @endif

      @if($invoice->paid_total > 0)
      <table class="t-row paid-row"><tr>
        <td class="t-label">Paid:</td>
        <td class="t-value">{{ $currency }}{{ number_format($invoice->paid_total, 2) }}</td>
      </tr></table>
      @endif

      @if($invoice->balance_due > 0)
      <table class="t-row amount-due"><tr>
        <td class="t-label">Balance Due:</td>
        <td class="t-value">{{ $currency }}{{ number_format($invoice->balance_due, 2) }}</td>
      </tr></table>
      @endif

    </div>
  </div>

  <!-- Notes -->
  @if($invoice->notes)
  <div class="notes-section">
    <div class="notes-label">Notes</div>
    <div class="notes-text">{!! nl2br(e($invoice->notes)) !!}</div>
  </div>
  @endif

  @if(isset($stamp) && $stamp)
    <div class="status-stamp" style="
      color: {{ $stamp['color'] }};
      border: 10px solid {{ $stamp['color'] }};
      padding: {{ strlen($stamp['text']) > 8 ? '8px 16px' : '10px 28px' }};
      font-size: {{ strlen($stamp['text']) > 8 ? '52px' : '72px' }};
    ">{{ $stamp['text'] }}</div>
  @endif

  <!-- Footer -->
  <div class="footer">
    Thank you for choosing {{ $appName }}
  </div>

</div>
</body>
</html>