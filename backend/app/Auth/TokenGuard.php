<?php

namespace App\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use App\Models\User;

class TokenGuard implements Guard
{
    use GuardHelpers;

    protected $request;
    protected $inputKey = 'api_token';

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();

        if ($token) {
            // Hash the token to match database storage
            $hashedToken = hash('sha256', $token);
            
            $personalAccessToken = PersonalAccessToken::where('token', $hashedToken)
                ->where('tokenable_type', User::class)
                ->first();

            if ($personalAccessToken) {
                $this->user = $personalAccessToken->tokenable;
                return $this->user;
            }
        }

        return null;
    }

    protected function getTokenForRequest()
    {
        $token = $this->request->bearerToken();

        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }

        return $token;
    }

    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        return ! is_null($this->user());
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
