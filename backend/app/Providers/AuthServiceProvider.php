<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\TokenGuard;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Crop::class => \App\Policies\CropPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register the custom token guard driver
        Auth::extend('token', function ($app, $name, array $config) {
            return new TokenGuard(
                Auth::createUserProvider($config['provider']), 
                $app['request']
            );
        });
    }
}
