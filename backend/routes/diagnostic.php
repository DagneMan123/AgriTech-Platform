<?php

/**
 * Diagnostic Routes - Add these to test and debug the API
 * 
 * Usage: 
 * 1. Uncomment the route group in routes/api.php:
 *    include 'diagnostic.php';
 * 
 * 2. Then access:
 *    GET /api/diagnostic/health
 *    GET /api/diagnostic/routes
 *    GET /api/diagnostic/auth (with Authorization header)
 */

use Illuminate\Support\Facades\Route;

// Public diagnostic routes
Route::get('/diagnostic/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'debug' => config('app.debug'),
        'time' => now(),
    ]);
});

Route::get('/diagnostic/routes', function () {
    $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())->filter(function ($route) {
        return strpos($route->uri(), 'api/farmer/dashboard') !== false || strpos($route->uri(), 'farmer') !== false;
    })->map(function ($route) {
        return [
            'method' => implode('|', $route->methods),
            'path' => $route->uri(),
            'action' => $route->action['controller'] ?? 'Closure',
            'middleware' => $route->middleware(),
        ];
    })->values();

    return response()->json([
        'total_routes' => count(\Illuminate\Support\Facades\Route::getRoutes()),
        'farmer_routes' => $routes,
    ]);
});

Route::get('/diagnostic/config', function () {
    return response()->json([
        'auth' => [
            'default_guard' => config('auth.defaults.guard'),
            'guards' => array_keys(config('auth.guards')),
            'providers' => array_keys(config('auth.providers')),
        ],
        'app' => [
            'url' => config('app.url'),
            'debug' => config('app.debug'),
            'env' => config('app.env'),
        ],
    ]);
});

// Protected diagnostic routes
Route::middleware(['auth:token'])->group(function () {
    Route::get('/diagnostic/auth', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'guard' => 'token',
            'token_exists' => $user->currentAccessToken() !== null,
        ]);
    });

    Route::get('/diagnostic/user', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $farmer = \App\Models\Farmer::where('user_id', $user->id)->first();
        $farmsCount = \App\Models\Farm::where('farmer_id', $user->id)->count();

        return response()->json([
            'user_id' => $user->id,
            'role' => $user->role,
            'farmer_profile_exists' => !!$farmer,
            'farms_count' => $farmsCount,
            'prerequisites_met' => $user->role === 'farmer' && !!$farmer,
        ]);
    });
});
