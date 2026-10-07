<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        
        // List of allowed origins
        $allowedOrigins = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
        ];
        
        // Add FRONTEND_URL from environment
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        if ($frontendUrl && !in_array($frontendUrl, $allowedOrigins)) {
            $allowedOrigins[] = $frontendUrl;
        }
        
        // Add SANCTUM_CORS_ORIGINS from environment
        $sanctumOrigins = env('SANCTUM_CORS_ORIGINS', '');
        if ($sanctumOrigins) {
            $origins = array_map('trim', explode(',', $sanctumOrigins));
            $allowedOrigins = array_merge($allowedOrigins, $origins);
            $allowedOrigins = array_unique($allowedOrigins);
        }
        
        
        $responseOrigin = in_array($origin, $allowedOrigins) ? $origin : $frontendUrl;

        
        $corsHeaders = [
            'Access-Control-Allow-Origin' => $responseOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Expose-Headers' => 'Content-Type, Authorization, X-New-Token',
        ];

        // Handle OPTIONS preflight requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->withHeaders($corsHeaders);
        }

        // Add CORS headers to all responses
        $response = $next($request);

        foreach ($corsHeaders as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
