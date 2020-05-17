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

    private $finger;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Finger $finger)
    {
        $this->finger = $finger;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
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
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $modelInfo = array(
            'id' => $this->finger->id,
            'model' => $this->finger->model,
            'method' => $this->finger->method,
            'original' => json_decode($this->finger->original, true),
            'changes' => json_decode($this->finger->changes, true)
        );
        $alertColor = '#ff9f00';
        switch($this->finger->method) {
        case 'created':
            $alertColor = '#00B945';
            break;
        case 'deleted':
            $alertColor = '#BC001A';
            break;
        }

        return (new MailMessage)
            ->subject(env('LARALogs_MODEL_SUBJECT', '[LaraLogs Alert] A model has been ' . $modelInfo['method']))
            ->from(env('LARALogs_FROM_EMAIL', 'alerts@laraLogs.com'), env('LARALogs_FROM_NAME', 'LaraLogs Alerts'))
            ->view(
                'laraLogs::emails.model-changed', [
                'modelInfo' => $modelInfo,
                'alertColor' => $alertColor
                ]
            );
    }

    public function toSlack($notifiable)
    {
        $modelInfo = array(
            'id' => $this->finger->id,
            'model' => $this->finger->model,
            'original' => json_decode($this->finger->original, true),
            'changes' => json_decode($this->finger->changes, true)
        );
        
        switch($this->finger->method) {
        case 'updated':
            return (new SlackMessage)
                ->warning()
                ->attachment(
                    function ($attachment) use ($modelInfo) {
                        $columnsChanged = '';
                        foreach(array_keys($modelInfo['changes']) as $changedColumn) {
                            $columnsChanged .= '    • ' . $changedColumn . "\r\n";
                        }
                        $attachment->title($modelInfo['model'] . ' #' . $modelInfo['original']['id'], route('laraLogs::models.show', $modelInfo['id']))
                            ->content('A model on ' . url('/') . ' has been updated. The following columns have changed:' . "\r\n" . $columnsChanged)
                            ->markdown(['text']);
                    }
                );
            break;
        case 'created':
            return (new SlackMessage)
                ->success()
                ->attachment(
                    function ($attachment) use ($modelInfo) {
                        $attachment->title($modelInfo['model'] . ' #' . $modelInfo['original']['id'], route('laraLogs::models.show', $modelInfo['id']))
                            ->content('A model on ' . url('/') . ' has been created.');
                    }
                );
            break;
        case 'deleted':
            return (new SlackMessage)
                ->error()
                ->attachment(
                    function ($attachment) use ($modelInfo) {
                        $attachment->title($modelInfo['model'] . ' #' . $modelInfo['original']['id'], route('laraLogs::models.show', $modelInfo['id']))
                            ->content('A model on ' . url('/') . ' has been deleted.');
                    }
                );
            break;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
