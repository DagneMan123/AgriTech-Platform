<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the origin from the request
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
        
        // Check if origin is allowed
        $originAllowed = in_array($origin, $allowedOrigins);
        $responseOrigin = $originAllowed ? $origin : null;

        // CORS headers that will be added to every response
        $corsHeaders = [
            'Access-Control-Allow-Origin' => $responseOrigin ?? 'http://localhost:5173',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Expose-Headers' => 'Content-Type, Authorization',
        ];

        // Handle preflight (OPTIONS) requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->withHeaders($corsHeaders);
        }

        // Process the request
        $response = $next($request);

        // Add CORS headers to the response
        foreach ($corsHeaders as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}

