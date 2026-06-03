<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ListingModerated extends Notification
{
    use Queueable;

    public function __construct(public string $action, public string $listingTitle) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    private function verb(): string
    {
        return $this->action === 'removed' ? 'устгасан' : 'нуусан';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Таны зар модерацлагдлаа')
            ->line('"'.$this->listingTitle.'" зарыг дүрэм зөрчсөн тул '.$this->verb().'.')
            ->action('Миний зар', url('/my/zar'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'moderation',
            'title' => 'Зар модерацлагдсан',
            'message' => '"'.$this->listingTitle.'" зарыг '.$this->verb().'.',
            'url' => '/my/zar',
        ];
    }
}
