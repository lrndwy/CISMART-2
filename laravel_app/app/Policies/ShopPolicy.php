<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'seller', 'buyer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shop $shop): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSeller()) {
            return $shop->user_id === $user->id;
        }
        return $user->isBuyer() && $shop->isApproved();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Toko dibuat otomatis saat permohonan disetujui: penjual hanya boleh
        // membuat manual bila permohonannya disetujui dan ia belum punya toko.
        return $user->isSeller()
            && $user->sellerApplication()->where('status', 'approved')->exists()
            && ! $user->shops()->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shop $shop): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSeller()) {
            return $shop->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shop $shop): bool
    {
        // Only admin can delete shops
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shop $shop): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shop $shop): bool
    {
        return $user->isAdmin();
    }
}
