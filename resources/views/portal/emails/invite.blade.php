<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  body { font-family: Arial, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .header { background: #1E3A5F; padding: 28px 32px; display: flex; align-items: center; gap: 12px; }
  .header img { height: 36px; width: auto; object-fit: contain; }
  .header h1 { color: #fff; font-size: 18px; margin: 0; }
  .body { padding: 32px; }
  .body p { line-height: 1.6; margin-bottom: 16px; font-size: 14px; }
  .cta { text-align: center; margin: 28px 0; }
  .btn { display: inline-block; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; text-decoration: none; color: #fff; background: #1E3A5F; }
  .hint { font-size: 12px; color: #9ca3af; text-align: center; margin-top: 12px; }
  .divider { border: none; border-top: 1px solid #f3f4f6; margin: 24px 0; }
  .footer { padding: 20px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    @if($logoUrl)
      <img src="{{ $logoUrl }}" alt="{{ $appName }}" />
    @endif
    <h1>{{ $appName }}</h1>
  </div>
  <div class="body">
    <p>Hello {{ $customer->contact_name ?? $customer->company_name }},</p>
    <p>
      You've been invited to access the <strong>{{ $appName }} client portal</strong>.
      From the portal, you can view your invoices, quotations and bookings,
      make payments online, and accept or decline quotations — all in one place.
    </p>
    <div class="cta">
      <a href="{{ $inviteUrl }}" class="btn">Set Up Your Account →</a>
    </div>
    <p class="hint">This link expires in 72 hours. If you didn't expect this email, you can safely ignore it.</p>
    <hr class="divider" />
    <p style="font-size: 13px; color: #6b7280;">
      If the button doesn't work, copy and paste this link into your browser:<br/>
      <a href="{{ $inviteUrl }}" style="color: #1E3A5F; word-break: break-all;">{{ $inviteUrl }}</a>
    </p>
  </div>
  <div class="footer">{{ $appName }} client portal</div>
</div>
</body>
</html>
