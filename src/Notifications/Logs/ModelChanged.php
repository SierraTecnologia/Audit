<?php

namespace Audit\Notifications\Logs;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Audit\Models\Logs\Finger;

class ModelChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private Finger $finger;

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     *
     * @psalm-return array<empty, empty>
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
