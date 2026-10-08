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
        // Kepemilikan dicek lebih dulu. Satu akun bisa berperan buyer + seller,
        // dan penjual yang berbelanja di toko lain tidak memiliki produk pada
        // pesanan tersebut — tanpa cek ini halaman pembayarannya ditolak.
        return $user->id === $order->user_id
            || $user->hasRole('admin')
            || $this->sellsItemsIn($user, $order);
    }

    /**
     * Penjual berhak atas pesanan yang memuat produk dari tokonya.
     */
    protected function sellsItemsIn(User $user, Order $order): bool
    {
        return $user->hasRole('seller')
            && $order->items()->whereHas('shop', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();
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
        // Admin and seller can create orders
        return $user->hasAnyRole(['admin', 'seller']);
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Hanya admin dan penjual pemilik produk yang boleh mengubah pesanan.
        // Kepemilikan pesanan saja tidak cukup di sini: verifyPayment()
        // memakai method ini, jadi pembeli tidak boleh memverifikasi sendiri.
        return $user->hasRole('admin') || $this->sellsItemsIn($user, $order);
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
