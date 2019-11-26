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

    private $laraLogsRequest;

    private $requestInfo;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(LaraLogsRequest $laraLogsRequest)
    {
        $this->laraLogsRequest = $laraLogsRequest;

        $this->requestInfo = array(
            'id' => $laraLogsRequest->id,
            'method' => $laraLogsRequest->method,
            'uri' => $laraLogsRequest->uri,
            'ip' => $laraLogsRequest->ip,
            'execution_time' => floor(($laraLogsRequest->end_time - $laraLogsRequest->start_time) * 1000)
        );
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        switch($notifiable->notify_by) {
            case 'email':
                return ['mail'];
            break;
            case 'slack':
                return ['slack'];
            break;
            case 'email_slack':
                return ['mail', 'slack'];
            break;
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $content = 'A route on ' . url('/') . ' was requested.';
        if($notifiable->filter !== '*' && !is_numeric($notifiable->filter)) {
            $content .= " You're being notified because the route contains `" . $notifiable->filter . "`";
        }

        if(is_numeric($notifiable->filter)) {
            $content .= " You're being notified because the execution time exceeded your limit of " . $notifiable->filter . "ms.";
        }

        $alertColor = '#00B945';
        if(is_numeric($notifiable->filter) && $this->requestInfo['execution_time'] > intval($notifiable->filter)) {
            $alertColor = '#BC001A';
        }

        return (new MailMessage)
            ->subject(env('LARALogs_ROUTE_SUBJECT', '[LaraLogs Alert] A route has been requested'))
            ->from(env('LARALogs_FROM_EMAIL', 'alerts@laraLogs.com'), env('LARALogs_FROM_NAME', 'LaraLogs Alerts'))
            ->view('laraLogs::emails.route-requested', [
                'requestInfo' => $this->requestInfo,
                'content' => $content,
                'alertColor' => $alertColor
            ]);
    }

    public function toSlack($notifiable)
    {
        $content = 'A route on ' . url('/') . ' was requested.';
        if($notifiable->filter !== '*' && !is_numeric($notifiable->filter)) {
            $content .= " You're being notified because the route contains `" . $notifiable->filter . "`";
        }

        if(is_numeric($notifiable->filter)) {
            $content .= " You're being notified because the execution time exceeded your limit of " . $notifiable->filter . "ms.";
        }

        $status = 'success';
        if(is_numeric($notifiable->filter) && $this->requestInfo['execution_time'] > intval($notifiable->filter)) {
            $status = 'error';
        }

        $requestInfo = $this->requestInfo;

        return (new SlackMessage)
            ->{ $status }()
            ->attachment(function($attachment) use($requestInfo, $content) {
                $attachment->title('Request #' . $requestInfo['id'], route('laraLogs::requests.show', $requestInfo['id']))
                    ->content($content)
                    ->fields([
                        'Method' => $requestInfo['method'],
                        'From IP' => $requestInfo['ip'],
                        'Requested URI' => $requestInfo['uri'],
                        'Execution Time' => $requestInfo['execution_time'] . 'ms'
                    ]);
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
