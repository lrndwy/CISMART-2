<?php

namespace App\Policies;

use App\Models\SellerApplication;
use App\Models\User;

class SellerApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('seller-applications.view') || $user->isAdmin();
    }

    public function view(User $user, SellerApplication $sellerApplication): bool
    {
        return $user->isAdmin() || $user->id === $sellerApplication->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isBuyer();
    }

    public function update(User $user, SellerApplication $sellerApplication): bool
    {
        return $user->hasPermissionTo('seller-applications.manage') || $user->isAdmin();
    }

    public function delete(User $user, SellerApplication $sellerApplication): bool
    {
        return $user->isAdmin();
    }
}
