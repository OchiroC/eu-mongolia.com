<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewComment extends Notification
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Шинэ сэтгэгдэл хүлээгдэж байна')
            ->line('"'.($this->comment->post?->title ?? 'Мэдээ').'" дээр шинэ сэтгэгдэл бичигдлээ.')
            ->line(Str::limit($this->comment->body, 120))
            ->action('Сэтгэгдэл шалгах', url('/admin/comments'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'comment',
            'title' => 'Шинэ сэтгэгдэл',
            'message' => ($this->comment->post?->title ?? 'Мэдээ').' дээр сэтгэгдэл хүлээгдэж байна',
            'url' => '/admin/comments',
        ];
    }
}
