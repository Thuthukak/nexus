<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Invoice {{ $invoice->reference }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 13px;
    color: #1a1a2e;
    background: #ffffff;
    padding: 0;
  }

  .page {
    padding: 40px 48px;
    min-height: 100vh;
  }

  /* Header */
  .header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 40px;
    padding-bottom: 24px;
    border-bottom: 2px solid {{ $primaryColor }};
  }

  .logo img {
    max-height: 60px;
    max-width: 180px;
  }

  .logo-text {
    font-size: 22px;
    font-weight: 700;
    color: {{ $primaryColor }};
    letter-spacing: -0.5px;
  }

  .invoice-title {
    text-align: right;
  }

  .invoice-title h1 {
    font-size: 28px;
    font-weight: 700;
    color: {{ $primaryColor }};
    letter-spacing: -1px;
  }

  .invoice-title .reference {
    font-size: 14px;
    color: #6b7280;
    margin-top: 4px;
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

  /* Addresses */
  .addresses {
    display: flex;
    justify-content: space-between;
    margin-bottom: 36px;
    gap: 40px;
  }

  .address-block {
    flex: 1;
  }

  .address-block .label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9ca3af;
    margin-bottom: 8px;
  }

  .address-block .company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
  }

  .address-block p {
    color: #6b7280;
    line-height: 1.6;
    font-size: 12px;
  }

  .invoice-meta {
    text-align: right;
  }

  .meta-table {
    margin-left: auto;
  }

  .meta-table tr td {
    padding: 3px 0;
    font-size: 12px;
  }

  .meta-table tr td:first-child {
    color: #9ca3af;
    padding-right: 20px;
    text-align: right;
  }

  .meta-table tr td:last-child {
    font-weight: 600;
    color: #111827;
    text-align: right;
  }

  /* Line items table */
  .items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
  }

  .items-table thead tr {
    background-color: {{ $primaryColor }};
  }

  .items-table thead th {
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #ffffff;
  }

  .items-table thead th.text-right {
    text-align: right;
  }

  .items-table tbody tr {
    border-bottom: 1px solid #f3f4f6;
  }

  .items-table tbody tr:nth-child(even) {
    background-color: #f9fafb;
  }

  .items-table tbody td {
    padding: 11px 14px;
    font-size: 12px;
    color: #374151;
    vertical-align: top;
  }

  .items-table tbody td.text-right {
    text-align: right;
  }

  .items-table tbody td.description {
    color: #111827;
    font-weight: 500;
  }

  /* Totals */
  .totals-section {
    margin-top: 0;
    border-top: 2px solid #f3f4f6;
  }

  .totals-table {
    width: 280px;
    margin-left: auto;
    border-collapse: collapse;
  }

  .totals-table tr td {
    padding: 8px 14px;
    font-size: 12px;
  }

  .totals-table tr td:first-child {
    color: #6b7280;
  }

  .totals-table tr td:last-child {
    text-align: right;
    font-weight: 600;
    color: #111827;
  }

  .totals-table .total-row td {
    font-size: 15px;
    font-weight: 700;
    color: {{ $primaryColor }};
    border-top: 2px solid {{ $primaryColor }};
    padding-top: 10px;
  }

  .totals-table .balance-row td {
    font-size: 13px;
    font-weight: 700;
    color: #dc2626;
  }

  .totals-table .paid-row td {
    color: #059669;
    font-size: 12px;
  }

  /* Notes */
  .notes-section {
    margin-top: 32px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 6px;
    border-left: 3px solid {{ $primaryColor }};
  }

  .notes-section .label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9ca3af;
    margin-bottom: 6px;
  }

  .notes-section p {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.6;
  }

  /* PAID stamp */
  .paid-stamp {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-35deg);
    font-size: 80px;
    font-weight: 900;
    color: rgba(5, 150, 105, 0.15);
    border: 12px solid rgba(5, 150, 105, 0.15);
    border-radius: 16px;
    padding: 10px 30px;
    letter-spacing: 8px;
    text-transform: uppercase;
    pointer-events: none;
    z-index: 1000;
    white-space: nowrap;
  }

  .part-paid-stamp {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-35deg);
    font-size: 55px;
    font-weight: 900;
    color: rgba(217, 119, 6, 0.15);
    border: 10px solid rgba(217, 119, 6, 0.15);
    border-radius: 16px;
    padding: 10px 24px;
    letter-spacing: 4px;
    text-transform: uppercase;
    pointer-events: none;
    z-index: 1000;
    white-space: nowrap;
  }

  /* Footer */
  .footer {
    margin-top: 48px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
    font-size: 11px;
    color: #9ca3af;
  }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
    <div class="logo">
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $appName }}"/>
      @else
        <div class="logo-text">{{ $appName }}</div>
      @endif
    </div>
    <div class="invoice-title">
      <h1>INVOICE</h1>
      <div class="reference">{{ $invoice->reference }}</div>
      <div class="badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</div>
    </div>
  </div>

  <!-- Addresses + Meta -->
  <div class="addresses">
    <div class="address-block">
      <div class="label">Bill To</div>
      <div class="company">{{ $invoice->customer->company_name }}</div>
      @if($invoice->customer->contact_name)
        <p>{{ $invoice->customer->contact_name }}</p>
      @endif
      @if($invoice->customer->email)
        <p>{{ $invoice->customer->email }}</p>
      @endif
      @if($invoice->customer->vat_number)
        <p>VAT: {{ $invoice->customer->vat_number }}</p>
      @endif
      @if($invoice->customer->address)
        @php $addr = $invoice->customer->address; @endphp
        @if(!empty($addr['line1'])) <p>{{ $addr['line1'] }}</p> @endif
        @if(!empty($addr['line2'])) <p>{{ $addr['line2'] }}</p> @endif
        @if(!empty($addr['city'])) <p>{{ $addr['city'] }}@if(!empty($addr['code'])), {{ $addr['code'] }}@endif</p> @endif
      @endif
    </div>

    <div class="address-block invoice-meta">
      <table class="meta-table">
        <tr>
          <td>Issue Date</td>
          <td>{{ $invoice->issue_date->format('d F Y') }}</td>
        </tr>
        <tr>
          <td>Due Date</td>
          <td>{{ $invoice->due_date->format('d F Y') }}</td>
        </tr>
        <tr>
          <td>Currency</td>
          <td>{{ $invoice->currency }}</td>
        </tr>
        @if($invoice->customer->vat_number)
        <tr>
          <td>Customer VAT</td>
          <td>{{ $invoice->customer->vat_number }}</td>
        </tr>
        @endif
      </table>
    </div>
  </div>

  <!-- Line items -->
  <table class="items-table">
    <thead>
      <tr>
        <th style="width:45%">Description</th>
        <th class="text-right" style="width:10%">Qty</th>
        <th class="text-right" style="width:17%">Unit Price</th>
        <th class="text-right" style="width:10%">Tax %</th>
        <th class="text-right" style="width:18%">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->lines as $line)
      <tr>
        <td class="description">{{ $line->description }}</td>
        <td class="text-right">{{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }}</td>
        <td class="text-right">{{ $currency }} {{ number_format($line->unit_price, 2) }}</td>
        <td class="text-right">{{ $line->tax_rate }}%</td>
        <td class="text-right">{{ $currency }} {{ number_format($line->line_total, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals-section">
    <table class="totals-table">
      <tr>
        <td>Subtotal</td>
        <td>{{ $currency }} {{ number_format($invoice->subtotal, 2) }}</td>
      </tr>
      <tr>
        <td>Tax</td>
        <td>{{ $currency }} {{ number_format($invoice->tax_total, 2) }}</td>
      </tr>
      <tr class="total-row">
        <td>Total</td>
        <td>{{ $currency }} {{ number_format($invoice->total, 2) }}</td>
      </tr>
      @if($invoice->paid_total > 0)
      <tr class="paid-row">
        <td>Paid</td>
        <td>{{ $currency }} {{ number_format($invoice->paid_total, 2) }}</td>
      </tr>
      @endif
      @if($invoice->balance_due > 0)
      <tr class="balance-row">
        <td>Balance Due</td>
        <td>{{ $currency }} {{ number_format($invoice->balance_due, 2) }}</td>
      </tr>
      @endif
    </table>
  </div>

  @if($invoice->notes)
  <div class="notes-section">
    <div class="label">Notes</div>
    <p>{{ $invoice->notes }}</p>
  </div>
  @endif

  @if($showPaidStamp ?? false)
    @if($invoice->status === 'paid')
      <div class="paid-stamp">PAID</div>
    @elseif($invoice->status === 'part_paid')
      <div class="part-paid-stamp">PART PAID</div>
    @endif
  @endif

  <!-- Footer -->
  <div class="footer">
    <p>{{ $appName }} &mdash; Generated {{ now()->format('d F Y') }}</p>
  </div>

</div>
</body>
</html>
