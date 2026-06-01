<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserCreatedNotification extends BaseNotification
{
    public function __construct(
        public readonly User $newUser
    ) {}

    public function notificationType(): string
    {
        return 'user.created';
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->buildDatabasePayload(
            type:   'user.created',
            title:  'New User Added',
            body:   "{$this->newUser->name} ({$this->newUser->email}) has been added to the platform.",
            module: 'Core',
            icon:   'user-plus',
            colour: 'blue',
            action: [
                'label' => 'View User',
                'url'   => "/users/{$this->newUser->id}",
            ],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New User Created')
            ->line("{$this->newUser->name} has been added to the platform.")
            ->action('View User', url("/users/{$this->newUser->id}"));
    }
}
