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
        // Apply CORS middleware globally - MUST be first to handle preflight requests
        $middleware->prepend(\App\Http\Middleware\CorsMiddleware::class);
        
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

        // IMPORTANT: These middleware should NOT run on login endpoint
        // They are expensive and should only run on authenticated routes
        // Register them only for routes that need them
        
        // Ensure notifications table exists
        $middleware->append(\App\Http\Middleware\EnsureNotificationsTableExists::class);

        // Auto-fix database constraints on first API request
        $middleware->append(\App\Http\Middleware\AutoFixConstraints::class);
        
        // Auto-fix crops table
        $middleware->append(\App\Http\Middleware\AutoFixCropsTable::class);
        
        // Ensure crop activities table exists
        $middleware->append(\App\Http\Middleware\EnsureCropActivitiesTableExists::class);
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