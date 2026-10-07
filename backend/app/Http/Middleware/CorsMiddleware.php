<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * The allowed origins for CORS requests
     */
    protected array $allowedOrigins = [
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin', '');
        
        // Determine the origin to send back
        $responseOrigin = in_array($origin, $this->allowedOrigins, true) 
            ? $origin 
            : 'http://localhost:5173';

        // Handle preflight requests (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            return response('', Response::HTTP_NO_CONTENT)
                ->header('Access-Control-Allow-Origin', $responseOrigin)
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
                ->header('Access-Control-Allow-Headers', 'DNT, X-CustomHeader, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Authorization, Accept')
                ->header('Access-Control-Max-Age', '1728000');
        }

        // Process the request
        $response = $next($request);

        // Add CORS headers to the response
        return $response
            ->header('Access-Control-Allow-Origin', $responseOrigin)
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD')
            ->header('Access-Control-Allow-Headers', 'DNT, X-CustomHeader, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Authorization, Accept')
            ->header('Access-Control-Expose-Headers', 'Authorization, X-New-Token');
    }
}
