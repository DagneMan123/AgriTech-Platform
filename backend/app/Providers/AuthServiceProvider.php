<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Crop::class => \App\Policies\CropPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}

