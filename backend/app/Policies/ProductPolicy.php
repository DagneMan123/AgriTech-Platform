<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine if user can view product
     */
    public function view(User $user, Product $product): bool
    {
        return true; // Products are public for viewing
    }

    /**
     * Determine if user can create product
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['farmer', 'supplier']);
    }

    /**
     * Determine if user can update product
     */
    public function update(User $user, Product $product): bool
    {
        return $user->id === $product->farmer_id || $user->id === $product->supplier_id || $user->role === 'admin';
    }

    /**
     * Determine if user can delete product
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->farmer_id || $user->id === $product->supplier_id || $user->role === 'admin';
    }
}
