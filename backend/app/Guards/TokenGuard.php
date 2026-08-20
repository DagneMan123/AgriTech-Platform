<?php

namespace App\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;

class TokenGuard implements Guard
{
    use GuardHelpers;

    protected $request;
    protected $provider;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    /**
     * Get the currently authenticated user.
     */
    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();

        if ($token) {
            $hashedToken = hash('sha256', $token);
            
            // Find the token in the database
            $personalAccessToken = PersonalAccessToken::where('token', $hashedToken)->first();
            
            if ($personalAccessToken) {
                $this->user = $personalAccessToken->tokenable;
                return $this->user;
            }
        }

        return null;
    }

    /**
     * Get the token from the request.
     */
    protected function getTokenFromRequest()
    {
        $token = $this->request->bearerToken();

        if (empty($token)) {
            $token = $this->request->getPassword();
        }

        return $token;
    }

    /**
     * Validate a user's credentials.
     */
    public function validate(array $credentials = [])
    {
        return false;
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth or token.
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        return false;
    }
}
