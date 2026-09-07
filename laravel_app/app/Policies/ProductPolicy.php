<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        // Admin and sellers can view products list
        return $user->hasRole(['admin', 'seller']);
    }

    public function view(User $user, Product $product): bool
    {
        // Admin can view all, sellers can view their own shop's products
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('seller')) {
            return $user->shops()->where('id', $product->shop_id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Admin and sellers can create products
        return $user->hasRole(['admin', 'seller']);
    }

    public function update(User $user, Product $product): bool
    {
        // Admin can update all, sellers can update their own shop's products
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('seller')) {
            return $user->shops()->where('id', $product->shop_id)->exists();
        }

        return false;
    }

    public function delete(User $user, Product $product): bool
    {
        // Same as update
        return $this->update($user, $product);
    }

    public function restore(User $user, Product $product): bool
    {
        // Same as update
        return $this->update($user, $product);
    }

    public function forceDelete(User $user, Product $product): bool
    {
        // Only admin can force delete
        return $user->hasRole('admin');
    }
}
