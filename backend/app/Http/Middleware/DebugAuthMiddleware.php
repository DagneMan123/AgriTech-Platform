<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if ($token) {
            Log::info('Token present in request', [
                'path' => $request->path(),
                'token_present' => true,
                'token_length' => strlen($token),
            ]);
            
            $hashedToken = hash('sha256', $token);
            $found = \App\Models\PersonalAccessToken::where('token', $hashedToken)->exists();
            
            Log::info('Token validation', [
                'path' => $request->path(),
                'token_found_in_db' => $found,
            ]);
        } else {
            Log::warning('No token in request', [
                'path' => $request->path(),
            ]);
        }

        return $next($request);
    }
}
