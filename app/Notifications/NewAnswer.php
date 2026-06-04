<?php

namespace App\Notifications;

use App\Models\Answer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewAnswer extends Notification
{
    use Queueable;

    public function __construct(public Answer $answer, public string $questionSlug, public string $questionTitle) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Таны асуултад хариулт ирлээ')
            ->line('"'.$this->questionTitle.'" асуултад шинэ хариулт ирлээ:')
            ->line('"'.Str::limit($this->answer->body, 120).'"')
            ->action('Хариултыг үзэх', url('/questions/'.$this->questionSlug));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'answer',
            'title' => 'Шинэ хариулт',
            'message' => '"'.Str::limit($this->questionTitle, 50).'" асуултад хариулт ирлээ',
            'url' => '/questions/'.$this->questionSlug,
        ];
    }
}
