<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReport extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Шинэ гомдол ирлээ')
            ->line('"'.($this->report->listing?->title ?? 'Зар').'" дээр шинэ гомдол ирлээ.')
            ->action('Модерац үзэх', url('/admin/reports'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report',
            'title' => 'Шинэ гомдол',
            'message' => ($this->report->listing?->title ?? 'Зар').' дээр гомдол ирлээ',
            'url' => '/admin/reports',
        ];
    }
}
