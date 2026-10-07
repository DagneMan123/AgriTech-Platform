<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {
        // Get the authenticated user.
        $user = $request->user();

        // User is not authenticated.
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // No roles were provided to the middleware.
        if (empty($roles)) {
            return response()->json([
                'message' => 'No role specified.',
            ], 403);
        }

        // Get user's role.
        $userRole = $user->role;

        // Check whether the user's role is allowed.
        if (!in_array($userRole, $roles, true)) {
            return response()->json([
                'message' => 'Forbidden.',
                'required_roles' => $roles,
                'user_role' => $userRole,
            ], 403);
        }

        // Continue to the requested controller/route.
        return $next($request);
    }
}
