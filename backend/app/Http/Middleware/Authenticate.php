<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Always return null for API requests to trigger 401 JSON response
        return null;
    }

    /**
     * Get the guards to check for authentication.
     */
    protected function guards(): array
    {
        return ['api'];
    }
}
