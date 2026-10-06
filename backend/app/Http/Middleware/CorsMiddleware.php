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
        
        $allowedOrigins = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
        ];
        
        $frontendUrl = env('FRONTEND_URL');
        if ($frontendUrl && !in_array($frontendUrl, $allowedOrigins)) {
            $allowedOrigins[] = $frontendUrl;
        }
        
        $isOriginAllowed = in_array($origin, $allowedOrigins);
        $responseOrigin = $isOriginAllowed ? $origin : $frontendUrl;

        $corsHeaders = [
            'Access-Control-Allow-Origin' => $responseOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Expose-Headers' => 'Content-Type, Authorization',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->withHeaders($corsHeaders);
        }

        $response = $next($request);

        foreach ($corsHeaders as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}

