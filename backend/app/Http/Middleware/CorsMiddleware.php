<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Get origin
        $origin = $request->header('Origin');
        
        // Allowed origins
        $allowedOrigins = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
        ];
        
        $responseOrigin = (in_array($origin, $allowedOrigins)) ? $origin : 'http://localhost:5173';

        // If it's a preflight request, return early with CORS headers
        if ($request->isMethod('OPTIONS')) {
            return response()
                ->header('Access-Control-Allow-Origin', $responseOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token')
                ->header('Access-Control-Max-Age', '86400')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        // For non-preflight requests, get the response and add CORS headers
        $response = $next($request);

        $response->header('Access-Control-Allow-Origin', $responseOrigin)
                 ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
                 ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token')
                 ->header('Access-Control-Expose-Headers', 'Content-Type')
                 ->header('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}

