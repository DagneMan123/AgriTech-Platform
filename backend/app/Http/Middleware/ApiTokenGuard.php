<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use App\Models\User;

class ApiTokenGuard
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'errors' => []
            ], 401);
        }

        $hashedToken = hash('sha256', $token);
        $personalAccessToken = PersonalAccessToken::where('token', $hashedToken)->first();

        if (!$personalAccessToken) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'errors' => []
            ], 401);
        }

        $user = User::find($personalAccessToken->tokenable_id);

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'errors' => []
            ], 401);
        }

        auth('api')->setUser($user);

        return $next($request);
    }
}
