<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>Quotation {{ $quotation->reference }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #1a1a2e; background: #fff; padding: 0; }
  .page { padding: 40px 48px; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; padding-bottom: 24px; border-bottom: 2px solid {{ $primaryColor }}; }
  .logo img { max-height: 60px; max-width: 180px; }
  .logo-text { font-size: 22px; font-weight: 700; color: {{ $primaryColor }}; }
  .quote-title { text-align: right; }
  .quote-title h1 { font-size: 28px; font-weight: 700; color: {{ $primaryColor }}; letter-spacing: -1px; }
  .quote-title .reference { font-size: 14px; color: #6b7280; margin-top: 4px; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px; }
  .badge-draft     { background: #f3f4f6; color: #6b7280; }
  .badge-sent      { background: #dbeafe; color: #2563eb; }
  .badge-accepted  { background: #d1fae5; color: #059669; }
  .badge-declined  { background: #fee2e2; color: #dc2626; }
  .badge-expired   { background: #f3f4f6; color: #6b7280; }
  .badge-converted { background: #ede9fe; color: #7c3aed; }
  .addresses { display: flex; justify-content: space-between; margin-bottom: 36px; gap: 40px; }
  .address-block { flex: 1; }
  .address-block .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 8px; }
  .address-block .company { font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 4px; }
  .address-block p { color: #6b7280; line-height: 1.6; font-size: 12px; }
  .meta-table { margin-left: auto; }
  .meta-table tr td { padding: 3px 0; font-size: 12px; }
  .meta-table tr td:first-child { color: #9ca3af; padding-right: 20px; text-align: right; }
  .meta-table tr td:last-child { font-weight: 600; color: #111827; text-align: right; }
  .valid-notice { background: #fffbeb; border-left: 3px solid #f59e0b; padding: 8px 14px; margin-bottom: 20px; font-size: 12px; color: #92400e; border-radius: 0 4px 4px 0; }
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
  .items-table thead tr { background-color: {{ $primaryColor }}; }
  .items-table thead th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #fff; }
  .items-table thead th.text-right { text-align: right; }
  .items-table tbody tr { border-bottom: 1px solid #f3f4f6; }
  .items-table tbody tr:nth-child(even) { background-color: #f9fafb; }
  .items-table tbody td { padding: 11px 14px; font-size: 12px; color: #374151; vertical-align: top; }
  .items-table tbody td.text-right { text-align: right; }
  .items-table tbody td.description { color: #111827; font-weight: 500; }
  .totals-section { margin-top: 0; border-top: 2px solid #f3f4f6; }
  .totals-table { width: 280px; margin-left: auto; border-collapse: collapse; }
  .totals-table tr td { padding: 8px 14px; font-size: 12px; }
  .totals-table tr td:first-child { color: #6b7280; }
  .totals-table tr td:last-child { text-align: right; font-weight: 600; color: #111827; }
  .totals-table .total-row td { font-size: 15px; font-weight: 700; color: {{ $primaryColor }}; border-top: 2px solid {{ $primaryColor }}; padding-top: 10px; }
  .notes-section { margin-top: 28px; padding: 16px; background: #f9fafb; border-radius: 6px; border-left: 3px solid {{ $primaryColor }}; }
  .notes-section .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 6px; }
  .notes-section p { font-size: 12px; color: #6b7280; line-height: 1.6; }
  .terms-section { margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px; }
  .accept-cta { margin-top: 28px; text-align: center; padding: 16px; background: {{ $primaryColor }}10; border: 1px solid {{ $primaryColor }}30; border-radius: 8px; }
  .accept-cta p { font-size: 13px; color: #374151; margin-bottom: 8px; }
  .accept-cta .url { font-size: 12px; color: {{ $primaryColor }}; font-weight: 600; }
  .status-stamp { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-35deg); font-weight: 900; border-radius: 16px; letter-spacing: 6px; text-transform: uppercase; pointer-events: none; z-index: 1000; white-space: nowrap; }
  .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 11px; color: #9ca3af; }
</style>
</head>
<body>
<div class="page">

  @if(isset($stamp) && $stamp)
    <div class="status-stamp" style="color: {{ $stamp['color'] }}; border: 10px solid {{ $stamp['color'] }}; padding: {{ strlen($stamp['text']) > 8 ? '8px 16px' : '10px 28px' }}; font-size: {{ strlen($stamp['text']) > 8 ? '52px' : '72px' }};">{{ $stamp['text'] }}</div>
  @endif

  <!-- Header -->
  <div class="header">
    <div class="logo">
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $appName }}"/>
      @else
        <div class="logo-text">{{ $appName }}</div>
      @endif
    </div>
    <div class="quote-title">
      <h1>QUOTATION</h1>
      <div class="reference">{{ $quotation->reference }}</div>
      <div class="badge badge-{{ $quotation->status }}">{{ strtoupper($quotation->status) }}</div>
    </div>
  </div>

  <!-- Addresses + Meta -->
  <div class="addresses">
    <div class="address-block">
      <div class="label">Prepared For</div>
      <div class="company">{{ $quotation->customer->company_name }}</div>
      @if($quotation->customer->contact_name)<p>{{ $quotation->customer->contact_name }}</p>@endif
      @if($quotation->customer->email)<p>{{ $quotation->customer->email }}</p>@endif
      @if($quotation->customer->vat_number)<p>VAT: {{ $quotation->customer->vat_number }}</p>@endif
    </div>
    <div class="address-block" style="text-align:right;">
      <table class="meta-table">
        <tr><td>Quote Number</td><td>{{ $quotation->reference }}</td></tr>
        <tr><td>Issue Date</td><td>{{ $quotation->issue_date->format('d F Y') }}</td></tr>
        <tr><td>Valid Until</td><td>{{ $quotation->valid_until->format('d F Y') }}</td></tr>
        <tr><td>Currency</td><td>{{ $quotation->currency }}</td></tr>
      </table>
    </div>
  </div>

  @if($quotation->status === 'sent')
  <div class="valid-notice">
    This quotation is valid until <strong>{{ $quotation->valid_until->format('d F Y') }}</strong>.
    Please review and accept or decline before the expiry date.
  </div>
  @endif

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
      @foreach($quotation->lines as $line)
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
      <tr><td>Subtotal</td><td>{{ $currency }} {{ number_format($quotation->subtotal, 2) }}</td></tr>
      <tr><td>Tax</td><td>{{ $currency }} {{ number_format($quotation->tax_total, 2) }}</td></tr>
      <tr class="total-row"><td>Total</td><td>{{ $currency }} {{ number_format($quotation->total, 2) }}</td></tr>
    </table>
  </div>

  @if($quotation->notes)
  <div class="notes-section">
    <div class="label">Notes</div>
    <p>{{ $quotation->notes }}</p>
  </div>
  @endif

  @if($quotation->terms)
  <div class="terms-section">
    <div class="label" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-bottom:6px;">Terms & Conditions</div>
    <p style="font-size:12px;color:#6b7280;line-height:1.6;">{{ $quotation->terms }}</p>
  </div>
  @endif

  @if($quotation->status === 'sent' && $quotation->quote_url)
  <div class="accept-cta">
    <p>To accept or decline this quotation, please visit:</p>
    <div class="url">{{ $quotation->quote_url }}</div>
  </div>
  @endif

  <div class="footer">
    <p>{{ $appName }} &mdash; Generated {{ now()->format('d F Y') }}</p>
  </div>

</div>
</body>
</html>
