<?php

namespace Audit\Notifications\Logs;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Audit\Models\Logs\Finger;

class LogWritten extends Notification implements ShouldQueue
{
    use Queueable;

    private Finger $laraLogsLog;

    /**
     * @var (mixed|null)[]
     *
     * @psalm-var array{id: mixed, level: mixed, message: mixed, user_id: mixed|null, email: mixed|null}
     */
    private array $requestInfo;

    /**
     * @var string[][]
     *
     * @psalm-var array{error: array{0: string, 1: string, 2: string, 3: string}, warning: array{0: string, 1: string}, success: array{0: string, 1: string}}
     */
    private array $notificationLevels;

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
