<?php

namespace App\Policies;

use App\Models\Crop;
use App\Models\User;

class CropPolicy
{
    /**
     * Determine whether the user can view the crop.
     */
    public function view(User $user, Crop $crop): bool
    {
        // User can view their own crops
        return $crop->farm->farmer_id === $user->id;
    }

    /**
     * Determine whether the user can update the crop.
     */
    public function update(User $user, Crop $crop): bool
    {
        // User can update their own crops
        return $crop->farm->farmer_id === $user->id;
    }

    /**
     * Determine whether the user can delete the crop.
     */
    public function delete(User $user, Crop $crop): bool
    {
        // User can delete their own crops
        return $crop->farm->farmer_id === $user->id;
    }
}
