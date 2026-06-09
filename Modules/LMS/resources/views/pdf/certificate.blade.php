<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    background: #fff;
    width: 297mm;
    height: 210mm;
  }
  .page {
    width: 100%;
    height: 210mm;
    padding: 20mm 25mm;
    position: relative;
    background: linear-gradient(135deg, #f8fafc 0%, #fff 50%, #f8fafc 100%);
    border: 12px solid {{ $primaryColor }};
    box-shadow: inset 0 0 0 4px #fff, inset 0 0 0 6px {{ $primaryColor }};
  }
  .corner {
    position: absolute;
    width: 60px;
    height: 60px;
    border: 4px solid {{ $primaryColor }};
    opacity: 0.3;
  }
  .corner-tl { top: 20px; left: 20px; border-right: none; border-bottom: none; }
  .corner-tr { top: 20px; right: 20px; border-left: none; border-bottom: none; }
  .corner-bl { bottom: 20px; left: 20px; border-right: none; border-top: none; }
  .corner-br { bottom: 20px; right: 20px; border-left: none; border-top: none; }
  .header {
    text-align: center;
    margin-bottom: 12px;
    border-bottom: 2px solid {{ $primaryColor }}20;
    padding-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
  }
  .header img { height: 48px; width: auto; object-fit: contain; }
  .header .org-name { font-size: 20px; font-weight: 700; color: {{ $primaryColor }}; }
  .cert-label {
    text-align: center;
    font-size: 11px;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: #9ca3af;
    margin-bottom: 10px;
  }
  .cert-title {
    text-align: center;
    font-size: 42px;
    font-weight: 700;
    color: {{ $primaryColor }};
    letter-spacing: -1px;
    margin-bottom: 12px;
  }
  .awarded-to {
    text-align: center;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 8px;
  }
  .student-name {
    text-align: center;
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    border-bottom: 2px solid {{ $primaryColor }};
    padding-bottom: 6px;
    margin: 0 40px 12px;
  }
  .completed-text {
    text-align: center;
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 6px;
  }
  .course-name {
    text-align: center;
    font-size: 20px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
  }
  .footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 16px;
  }
  .signature-block { text-align: center; }
  .signature-line {
    width: 160px;
    border-top: 1px solid #374151;
    padding-top: 6px;
    font-size: 11px;
    color: #6b7280;
  }
  .cert-meta { text-align: right; font-size: 10px; color: #9ca3af; line-height: 1.6; }
  .seal {
    width: 80px;
    height: 80px;
    border: 4px solid {{ $primaryColor }};
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 4px;
    color: {{ $primaryColor }};
    font-weight: 700;
    font-size: 9px;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 8px;
  }
</style>
</head>
<body>
<div class="page">
  <div class="corner corner-tl"></div>
  <div class="corner corner-tr"></div>
  <div class="corner corner-bl"></div>
  <div class="corner corner-br"></div>

  <div class="header">
    @if($logoUrl)
      <img src="{{ $logoUrl }}" />
    @endif
    <div class="org-name">{{ $appName }}</div>
  </div>

  <div class="cert-label">Certificate of Completion</div>
  <div class="cert-title">CERTIFICATE</div>
  <div class="awarded-to">This is to certify that</div>

  <div class="student-name">{{ $enrollment->student->name }}</div>

  <div class="completed-text">has successfully completed the course</div>
  <div class="course-name">{{ $enrollment->cohort->course->title }}</div>
  <div class="completed-text" style="font-size:12px;">
    {{ $enrollment->cohort->name }} &mdash;
    Completed {{ $enrollment->completed_at->format('d F Y') }}
  </div>

  <div class="footer">
    <div class="signature-block">
      <div class="seal">{{ $appName }}</div>
    </div>
    <div class="signature-block">
      <div class="signature-line">Authorised Signature</div>
    </div>
    <div class="cert-meta">
      Certificate No: {{ $certNumber }}<br/>
      Issued: {{ now()->format('d F Y') }}<br/>
      {{ $appName }}
    </div>
  </div>
</div>
</body>
</html>
