<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire';

    protected $description = 'Batalkan pesanan yang sudah melewati batas pembayaran/konfirmasi';

    public function handle(): int
    {
        $expired = Order::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
            ->where(function ($query) {
                // QRIS yang belum terverifikasi.
                $query->where(function ($q) {
                    $q->where('payment_method', 'qris')
                        ->whereIn('payment_status', ['unpaid', 'rejected']);
                })
                    // COD/pickup yang tak pernah dikonfirmasi penjual.
                    ->orWhere(function ($q) {
                        $q->where('payment_method', 'cod')
                            ->where('status', Order::STATUS_PENDING);
                    });
            })
            ->get();

        $cancelled = 0;

        foreach ($expired as $order) {
            if ($order->transitionTo(Order::STATUS_CANCELLED, null, 'Kedaluwarsa otomatis')) {
                $cancelled++;
            }
        }

        $this->info("Pesanan kedaluwarsa dibatalkan: {$cancelled}");

        return self::SUCCESS;
    }
}