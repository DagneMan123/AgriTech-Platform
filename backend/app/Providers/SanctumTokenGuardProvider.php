<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SanctumTokenGuardProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['auth']->extend('token', function ($app, $name, array $config) {
            return new \App\Guards\SanctumTokenGuard(
                $app['auth']->createUserProvider($config['provider'] ?? null),
                $app['request']
            );
        });
    }
}

