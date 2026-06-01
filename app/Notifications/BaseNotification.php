<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $type  = $this->notificationType();
        $prefs = $notifiable->notification_preferences ?? [];

        if ($prefs[$type]['email'] ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * The notification type key used for preference lookup.
     * e.g. 'invoice.approved', 'leave.submitted'
     */
    abstract public function notificationType(): string;

    /**
     * Standard database payload structure.
     * All notifications must follow this shape so the frontend
     * can render them without knowing the specific type.
     */
    protected function buildDatabasePayload(
        string  $type,
        string  $title,
        string  $body,
        string  $module,
        string  $icon,
        string  $colour,
        ?array  $action = null,
    ): array {
        return [
            'type'   => $type,
            'title'  => $title,
            'body'   => $body,
            'module' => $module,
            'icon'   => $icon,
            'colour' => $colour,
            'action' => $action,
        ];
    }
}
