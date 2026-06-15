<?php

declare(strict_types=1);

namespace App\Mail;

use App\Facades\Settings;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $inviteUrl,
        public readonly string $roleName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve been invited to ' . Settings::group('general')
                ->get('app_name', config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-invite',
            with: [
                'user'      => $this->user,
                'inviteUrl' => $this->inviteUrl,
                'roleName'  => $this->roleName,
                'appName'   => Settings::group('general')
                    ->get('app_name', config('app.name')),
                'logoUrl'   => Settings::group('general')
                    ->get('logo_url'),
            ],
        );
    }
}
