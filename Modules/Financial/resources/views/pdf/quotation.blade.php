<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Quotation {{ $quotation->reference }}</title>
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
    color: #1a1a1a;
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
  .badge-sent      { background: #dbeafe; color: #2563eb; }
  .badge-accepted  { background: #d1fae5; color: #059669; }
  .badge-declined  { background: #fee2e2; color: #dc2626; }
  .badge-expired   { background: #f3f4f6; color: #6b7280; }
  .badge-converted { background: #ede9fe; color: #7c3aed; }

  /* ── Divider ── */
  .divider { border: none; border-top: 1px solid #ccc; margin-bottom: 20px; }

  /* ── Prepared For + Meta ── */
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

  .valid-until-box {
    display: inline-table;
    background: #f0f0f0;
    border-radius: 4px;
    margin-top: 6px;
  }
  .valid-until-inner  { display: table-cell; padding: 5px 14px; }
  .valid-until-label  { font-size: 11px; font-weight: 700; line-height: 1.3; }
  .valid-until-value  { font-size: 14px; font-weight: 700; line-height: 1.3; }

  /* ── Valid notice ── */
  .valid-notice {
    background: #fffbeb;
    border-left: 3px solid #d97706;
    padding: 8px 14px;
    margin-bottom: 20px;
    font-size: 11px;
    color: #92400e;
    border-radius: 0 4px 4px 0;
  }

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

  /* ── Notes / Terms ── */
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

  .terms-section { margin-top: 20px; }
  .terms-label {
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 4px;
  }
  .terms-text {
    font-size: 11px;
    color: #333;
    line-height: 1.5;
  }

  /* ── Accept CTA ── */
  .accept-cta {
    margin-top: 28px;
    text-align: center;
    padding: 16px;
    background: #f7f7f7;
    border: 1px solid #ddd;
    border-radius: 6px;
  }
  .accept-cta p {
    font-size: 12px;
    color: #333;
    margin-bottom: 6px;
  }
  .accept-cta .url {
    font-size: 12px;
    color: {{ $primaryColor }};
    font-weight: 700;
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
        <div class="invoice-heading">QUOTATION</div>
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

  <!-- Prepared For + Quotation Meta -->
  <table class="meta-table">
    <tr>
      <td style="width: 50%;">
        <div class="bill-label">Prepared For</div>
        <div class="bill-name">{{ $quotation->customer->company_name }}</div>
        <div class="bill-detail">
          @if($quotation->customer->contact_name) {{ $quotation->customer->contact_name }}<br> @endif
          @if($quotation->customer->email)        {{ $quotation->customer->email }}<br> @endif
          @if($quotation->customer->vat_number)   VAT: {{ $quotation->customer->vat_number }}<br> @endif
          @if($quotation->customer->address)
            @php $addr = $quotation->customer->address; @endphp
            @if(!empty($addr['line1'])) {{ $addr['line1'] }}<br> @endif
            @if(!empty($addr['line2'])) {{ $addr['line2'] }}<br> @endif
            @if(!empty($addr['city'])) {{ $addr['city'] }}@if(!empty($addr['code'])), {{ $addr['code'] }}@endif @endif
          @endif
        </div>
      </td>
      <td style="text-align: right; vertical-align: top;">
        <div class="meta-right">
          <div class="meta-row"><span class="meta-label">Quote Number: </span>{{ $quotation->reference }}</div>
          <div class="meta-row"><span class="meta-label">Issue Date: </span>{{ $quotation->issue_date->format('F j, Y') }}</div>
          <div class="meta-row"><span class="meta-label">Valid Until: </span>{{ $quotation->valid_until->format('F j, Y') }}</div>
          <div class="meta-row"><span class="meta-label">Currency: </span>{{ $quotation->currency }}</div>
        </div>
        <div style="text-align: right; margin-top: 8px;">
          <div class="valid-until-box">
            <div class="valid-until-inner">
              <div class="valid-until-label">Total ({{ $quotation->currency }}):</div>
              <div class="valid-until-value">{{ $currency }} {{ number_format($quotation->total, 2) }}</div>
            </div>
          </div>
        </div>
      </td>
    </tr>
  </table>

  @if($quotation->status === 'sent')
  <div class="valid-notice">
    This quotation is valid until <strong>{{ $quotation->valid_until->format('F j, Y') }}</strong>. Please review and accept or decline before the expiry date.
  </div>
  @endif

  <!-- Line Items -->
  <table class="items-table">
    <thead>
      <tr>
        <th style="width: 40%;">Description</th>
        <th class="text-right" style="width: 12%;">Qty</th>
        <th class="text-right" style="width: 16%;">Unit Price</th>
        <th class="text-right" style="width: 12%;">Tax %</th>
        <th class="text-right" style="width: 20%;">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($quotation->lines as $line)
      <tr>
        <td>
          <div class="item-title">{{ $line->description }}</div>
        </td>
        <td class="text-right">{{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }}</td>
        <td class="text-right">{{ $currency }}{{ number_format($line->unit_price, 2) }}</td>
        <td class="text-right">{{ $line->tax_rate }}%</td>
        <td class="text-right">{{ $currency }}{{ number_format($line->line_total, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Totals -->
  <div class="clearfix">
    <div class="totals-inner">
      <table class="t-row"><tr>
        <td class="t-label">Subtotal:</td>
        <td class="t-value">{{ $currency }}{{ number_format($quotation->subtotal, 2) }}</td>
      </tr></table>
      <table class="t-row"><tr>
        <td class="t-label">Tax:</td>
        <td class="t-value">{{ $currency }}{{ number_format($quotation->tax_total, 2) }}</td>
      </tr></table>
      <table class="t-row grand-total"><tr>
        <td class="t-label">Total:</td>
        <td class="t-value">{{ $currency }}{{ number_format($quotation->total, 2) }}</td>
      </tr></table>
    </div>
  </div>

  <!-- Notes -->
  @if($quotation->notes)
  <div class="notes-section">
    <div class="notes-label">Notes</div>
    <div class="notes-text">{!! nl2br(e($quotation->notes)) !!}</div>
  </div>
  @endif

  <!-- Terms -->
  @if($quotation->terms)
  <div class="terms-section">
    <div class="terms-label">Terms &amp; Conditions</div>
    <div class="terms-text">{!! nl2br(e($quotation->terms)) !!}</div>
  </div>
  @endif

  <!-- Accept CTA -->
  @if($quotation->status === 'sent' && $quotation->quote_url)
  <div class="accept-cta">
    <p>To accept or decline this quotation, please visit:</p>
    <div class="url">{{ $quotation->quote_url }}</div>
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