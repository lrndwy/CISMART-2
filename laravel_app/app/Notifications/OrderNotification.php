<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $event,
        public array $extra = [],
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function title(): string
    {
        return match ($this->event) {
            'placed' => 'Pesanan baru masuk',
            'proof_uploaded' => 'Bukti pembayaran diunggah',
            'payment_verified' => 'Pembayaran terverifikasi',
            'payment_rejected' => 'Bukti pembayaran ditolak',
            'status_changed' => 'Status pesanan berubah',
            default => 'Pembaruan pesanan',
        };
    }

    public function body(): string
    {
        $number = $this->order->order_number;

        return match ($this->event) {
            'placed' => "Pesanan {$number} dibuat dan menunggu konfirmasi.",
            'proof_uploaded' => "Pembeli mengunggah bukti pembayaran untuk {$number}.",
            'payment_verified' => "Pembayaran pesanan {$number} sudah diverifikasi.",
            'payment_rejected' => "Bukti pembayaran {$number} ditolak. ".($this->extra['note'] ? 'Catatan: '.$this->extra['note'] : 'Silakan unggah ulang.'),
            'status_changed' => "Pesanan {$number} berubah dari {$this->extra['from']} menjadi {$this->extra['to']}.",
            default => "Ada pembaruan pada pesanan {$number}.",
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'event' => $this->event,
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => route('orders.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title().' — '.$this->order->order_number)
            ->line($this->body())
            ->action('Lihat pesanan', route('orders.index'));
    }
}