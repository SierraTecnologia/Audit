<?php

namespace Audit\Notifications\Logs;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Audit\Models\Logs\LaraLogsRequest;

class RouteRequested extends Notification implements ShouldQueue
{
    use Queueable;

    private LaraLogsRequest $laraLogsRequest;

    /**
     * @var (float|mixed)[]
     *
     * @psalm-var array{id: mixed, method: mixed, uri: mixed, ip: mixed, execution_time: float}
     */
    private array $requestInfo;

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
