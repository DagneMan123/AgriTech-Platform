<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate; // <- ይህንን አስገባ

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        // የፖሊሲ ምዝገባን እዚህ እናደርጋለን (ከቀድሞው AuthServiceProvider የመጣ)
        Gate::policy(\App\Models\Crop::class, \App\Policies\CropPolicy::class);
    }
}