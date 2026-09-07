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
        // Admin can view any order
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can view orders for their products
        if ($user->hasRole('seller')) {
            return $order->items()->whereHas('shop', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();
        }

        // Users can view their own orders
        return $user->id === $order->user_id;
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
        // Admin can update any order
        if ($user->hasRole('admin')) {
            return true;
        }

        // Seller can update orders for their products
        if ($user->hasRole('seller')) {
            return $order->items()->whereHas('shop', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->exists();
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
