<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DatabaseConstraintFixer;

class ConstraintFixerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * 
     * This provider automatically fixes the database foreign key constraint issue.
     * The farms table has farmer_id that incorrectly references farmers(id) instead of users(id).
     */
    public function boot(): void
    {
        // Attempt to fix constraints on every request
        // This ensures the constraint is fixed before any database operations
        try {
            DatabaseConstraintFixer::fixFarmsConstraint();
        } catch (\Exception $e) {
            // Silently fail - don't break the app
        }
    }

    public function register(): void
    {
        //
    }
}
