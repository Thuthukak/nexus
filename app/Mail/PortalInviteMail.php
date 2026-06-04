<?php

declare(strict_types=1);

namespace App\Mail;

use App\Facades\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Financial\app\Models\Customer;

class PortalInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Customer $customer,
        public readonly string   $inviteUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re invited to the ' . config('app.name') . ' client portal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'portal.emails.invite',
            with: [
                'customer'  => $this->customer,
                'inviteUrl' => $this->inviteUrl,
                'appName'   => Settings::group('general')->get('app_name', config('app.name')),
                'logoUrl'   => Settings::group('general')->get('logo_url'),
            ],
        );
    }
}
