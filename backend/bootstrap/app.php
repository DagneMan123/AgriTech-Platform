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
        // Register middleware aliases for route middleware groups
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // API rate limiting
        $middleware->api([
            'throttle:60,1',
        ]);

        // Auto-fix database constraints on first API request
        $middleware->append(\App\Http\Middleware\AutoFixConstraints::class);
        
        // Auto-fix crops table
        $middleware->append(\App\Http\Middleware\AutoFixCropsTable::class);
        
        // Apply CORS middleware globally
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\ConstraintFixerServiceProvider::class,
        \App\Providers\CropsTableFixerProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always return JSON for API requests
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();