<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        // Admin and seller can view orders
        return $user->hasAnyRole(['admin', 'seller']);
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Order kini milik satu toko: penjual pemilik toko itulah yang boleh melihat.
        if ($user->hasRole('seller')) {
            return $order->shop_id !== null
                && $user->shops()->whereKey($order->shop_id)->exists();
        }

        // Users can view their own orders
        return $user->id === $order->user_id;
    }

    /**
     * Pembeli membatalkan pesanannya sendiri yang belum diproses & belum terbayar.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], true)
            && $order->payment_status !== 'verified';
    }

    /**
     * Pembeli mengonfirmasi penerimaan (setelah penjual mengirim).
     */
    public function confirmReceipt(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && $order->status === Order::STATUS_SHIPPED;
    }

    public function uploadPaymentProof(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && $order->isQris();
    }

    public function verifyPayment(User $user, Order $order): bool
    {
        return $this->update($user, $order);
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        // Order dibuat lewat alur checkout (CheckoutPage) atau admin.
        return $user->hasAnyRole(['admin', 'seller', 'buyer']);
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('seller')) {
            return $order->shop_id !== null
                && $user->shops()->whereKey($order->shop_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Only admin can delete orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the order.
     */
    public function restore(User $user, Order $order): bool
    {
        // Only admin can restore orders
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the order.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        // Only admin can force delete orders
        return $user->hasRole('admin');
    }
}
