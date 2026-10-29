<?php

if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth($string, $start, $width, $trimmarker = '', $encoding = 'UTF-8') {
        $len = function_exists('mb_strlen') ? mb_strlen($string, $encoding) : strlen($string);
        if ($len <= $width) {
            return $string;
        }
        $markerLen = function_exists('mb_strlen') ? mb_strlen($trimmarker, $encoding) : strlen($trimmarker);
        $sub = function_exists('mb_substr') ? mb_substr($string, $start, $width - $markerLen, $encoding) : substr($string, $start, $width - $markerLen);
        return $sub . $trimmarker;
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // የ role ሚድልዌርን እዚህ ላይ እንደ አሊያስ እንመዝገበዋለን
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();