<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;

class FarmPolicy
{
    /**
     * Determine if user can view farm
     */
    public function view(User $user, Farm $farm): bool
    {
        return $user->id === $farm->farmer_id || $user->role === 'admin';
    }

    /**
     * Determine if user can create farm
     */
    public function create(User $user): bool
    {
        return $user->role === 'farmer';
    }

    /**
     * Determine if user can update farm
     */
    public function update(User $user, Farm $farm): bool
    {
        return $user->id === $farm->farmer_id || $user->role === 'admin';
    }

    /**
     * Determine if user can delete farm
     */
    public function delete(User $user, Farm $farm): bool
    {
        return $user->id === $farm->farmer_id || $user->role === 'admin';
    }
}
