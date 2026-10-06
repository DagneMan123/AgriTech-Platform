<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        $middleware->api([
            'throttle:60,1',
        ]);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\ConstraintFixerServiceProvider::class,
        \App\Providers\CropsTableFixerProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
