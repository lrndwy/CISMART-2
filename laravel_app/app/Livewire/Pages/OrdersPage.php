<?php

namespace App\Livewire\Pages;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrdersPage extends Component
{
    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login', ['redirect' => request()->fullUrl()]));
        }
    }

    /**
     * Pembeli membatalkan pesanan yang belum diproses & belum terbayar.
     */
    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()->where('user_id', Auth::id())->findOrFail($orderId);

        if (! in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], true)) {
            $this->dispatch('notify', ['message' => 'Pesanan sudah diproses, hubungi penjual untuk pembatalan.', 'type' => 'error']);

            return;
        }

        if ($order->payment_status === 'verified') {
            $this->dispatch('notify', ['message' => 'Pembayaran sudah terverifikasi. Hubungi penjual untuk pengembalian dana.', 'type' => 'error']);

            return;
        }

        if (! $order->transitionTo(Order::STATUS_CANCELLED, Auth::user(), 'Dibatalkan pembeli')) {
            $this->dispatch('notify', ['message' => 'Pesanan tidak bisa dibatalkan sekarang.', 'type' => 'error']);

            return;
        }

        $this->dispatch('notify', ['message' => 'Pesanan dibatalkan. Stok dikembalikan ke penjual.', 'type' => 'success']);
    }

    /**
     * Pembeli mengonfirmasi pesanan sudah diterima → status delivered.
     */
    public function confirmReceipt(int $orderId): void
    {
        $order = Order::query()->where('user_id', Auth::id())->findOrFail($orderId);

        if ($order->status !== Order::STATUS_SHIPPED) {
            $this->dispatch('notify', ['message' => 'Pesanan belum dikirim penjual.', 'type' => 'error']);

            return;
        }

        if (! $order->transitionTo(Order::STATUS_DELIVERED, Auth::user(), 'Diterima pembeli')) {
            $this->dispatch('notify', ['message' => 'Pesanan tidak bisa diselesaikan sekarang.', 'type' => 'error']);

            return;
        }

        $this->dispatch('notify', ['message' => 'Terima kasih! Pesanan selesai — kamu bisa mengulas produknya sekarang.', 'type' => 'success']);
    }

    public function markNotificationsRead(): void
    {
        Auth::user()?->unreadNotifications->markAsRead();
    }

    public function render()
    {
        $user = Auth::user();

        $orders = $user
            ? Order::query()->with('items')->where('user_id', $user->id)->latest()->get()
            : collect();

        $notifications = $user
            ? $user->notifications()->latest()->limit(10)->get()
            : collect();

        return view('livewire.pages.orders-page', compact('orders', 'notifications'));
    }
}