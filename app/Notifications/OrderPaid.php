<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaid extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing('event', 'tickets.ticketType');

        $mail = (new MailMessage)
            ->subject('Тасалбар баталгаажлаа')
            ->line('Таны захиалга баталгаажлаа: '.($this->order->event?->title ?? 'Эвент'))
            ->line('Захиалгын дугаар: '.$this->order->reference)
            ->line('Нийт дүн: '.$this->order->total.'€');

        // Тасалбар бүрийн QR кодыг хавсаргана (хаалган дээр шалгуулна).
        foreach ($this->order->tickets as $ticket) {
            $mail->line('• '.($ticket->ticketType?->name ?? 'Тасалбар').' — код: '.$ticket->code);
            $mail->attachData(\App\Support\Qr::png($ticket->code), 'ticket-'.$ticket->code.'.png', [
                'mime' => 'image/png',
            ]);
        }

        return $mail
            ->line('Хаалган дээр QR кодоо үзүүлнэ үү. (хавсаргасан зураг)')
            ->action('Тасалбар үзэх', url('/orders/'.$this->order->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order',
            'title' => 'Тасалбар баталгаажлаа',
            'message' => ($this->order->event?->title ?? 'Эвент').' — '.$this->order->total.'€',
            'url' => '/orders/'.$this->order->id,
        ];
    }
}
