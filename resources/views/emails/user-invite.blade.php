<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  body { font-family: Arial, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
  .wrapper { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
  .header { background: #1E3A5F; padding: 28px 32px; display: flex; align-items: center; gap: 12px; }
  .header img { height: 36px; width: auto; object-fit: contain; }
  .header h1  { color: #fff; font-size: 18px; margin: 0; }
  .body { padding: 32px; }
  .body p { line-height: 1.7; font-size: 14px; margin-bottom: 14px; }
  .role-badge { display: inline-block; background: #ede9fe; color: #6d28d9; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 20px; }
  .cta { text-align: center; margin: 28px 0; }
  .btn { display: inline-block; padding: 14px 32px; border-radius: 8px; font-weight: 700; font-size: 15px; text-decoration: none; color: #fff; background: #1E3A5F; }
  .expire-note { font-size: 12px; color: #9ca3af; text-align: center; margin-top: 8px; }
  .divider { border: none; border-top: 1px solid #f3f4f6; margin: 24px 0; }
  .url-fallback { font-size: 11px; color: #9ca3af; }
  .url-fallback a { color: #1E3A5F; word-break: break-all; }
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
    <p>Hi {{ $user->name }},</p>
    <p>
      You've been invited to join <strong>{{ $appName }}</strong>.
      Your account has been set up with the following role:
    </p>
    <div>
      <span class="role-badge">{{ $roleName }}</span>
    </div>
    <p>
      Click the button below to set your password and access your account.
      This link is valid for <strong>72 hours</strong>.
    </p>
    <div class="cta">
      <a href="{{ $inviteUrl }}" class="btn">Set Up My Account →</a>
    </div>
    <p class="expire-note">This link will expire in 72 hours.</p>
    <hr class="divider" />
    <p class="url-fallback">
      If the button doesn't work, copy and paste this link into your browser:<br/>
      <a href="{{ $inviteUrl }}">{{ $inviteUrl }}</a>
    </p>
  </div>
  <div class="footer">{{ $appName }} &mdash; You received this because an admin added you.</div>
</div>
</body>
</html>
