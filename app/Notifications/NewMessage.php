<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessage extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sender = $this->message->sender?->name ?? 'Хэрэглэгч';

        return (new MailMessage)
            ->subject('Шинэ зурвас — '.$sender)
            ->line($sender.' танд зурвас илгээлээ:')
            ->line('"'.Str::limit($this->message->body, 120).'"')
            ->action('Хариулах', url('/messages/'.$this->message->conversation_id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender?->name ?? 'Хэрэглэгч';

        return [
            'type' => 'message',
            'title' => 'Шинэ зурвас',
            'message' => $sender.': '.Str::limit($this->message->body, 60),
            'url' => '/messages/'.$this->message->conversation_id,
        ];
    }
}
