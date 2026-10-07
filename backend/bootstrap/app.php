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
        // ምንም አይነት Custom CorsMiddleware የለም። 
        // Laravel በውስጡ ባለው አብሮገነብ የ CORS ማስተካከያ በቂ ነው።
        
        $middleware->api([
            'throttle:60,1',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'api.token' => \App\Http\Middleware\ApiTokenGuard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // እዚህጋ withHeaders በመጠቀም ስታክ ሉፕ እንዳይፈጠር በቀጥታ እናስተካክለዋለን
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'An error occurred',
                ], 500);
            }
        });
    })->create();