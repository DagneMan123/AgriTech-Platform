<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if user can view order
     */
    public function view(User $user, Order $order): bool
    {
        // Buyer can view their orders
        if ($user->id === $order->buyer_id) {
            return true;
        }

        // Farmers can view orders containing their products
        if ($user->role === 'farmer') {
            return $order->items()->where('farmer_id', $user->farmer->id)->exists();
        }

        // Admin can view all orders
        return $user->role === 'admin';
    }

    /**
     * Determine if user can update order
     */
    public function update(User $user, Order $order): bool
    {
        // Farmers can update order status for their products
        if ($user->role === 'farmer') {
            return $order->items()->where('farmer_id', $user->farmer->id)->exists();
        }

        return $user->role === 'admin';
    }
}
