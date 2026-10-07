<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ለየትኛውም ጥያቄ (Options ጨምሮ) ትክክለኛውን የፍሮንቴንድ ዩአርኤል እንሰጣለን
        $headers = [
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
        ];

        // ለ Preflight (OPTIONS) ጥያቄዎች ወዲያውኑ የ 200 ምላሽ እንመልሳለን (ሉፕ እንዳይፈጠር)
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)->withHeaders($headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}