<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfessionalStatus extends Notification
{
    use Queueable;

    /** $action: approved | verified */
    public function __construct(public string $action, public string $name, public string $slug) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    private function text(): string
    {
        return $this->action === 'verified'
            ? 'Таны "'.$this->name.'" мэргэжилтний профайл баталгаажлаа ✓'
            : 'Таны "'.$this->name.'" мэргэжилтний профайл зөвшөөрөгдөж нийтлэгдлээ.';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Мэргэжилтний профайл шинэчлэгдлээ')
            ->line($this->text())
            ->action('Профайл үзэх', url('/professionals/'.$this->slug));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'moderation',
            'title' => $this->action === 'verified' ? 'Профайл баталгаажлаа' : 'Профайл нийтлэгдлээ',
            'message' => $this->text(),
            'url' => '/professionals/'.$this->slug,
        ];
    }
}
