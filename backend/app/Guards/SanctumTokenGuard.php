<?php

namespace App\Guards;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SanctumTokenGuard implements Guard
{
    use GuardHelpers;

    protected $request;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function authenticate()
    {
        if ($this->user() !== null) {
            return $this->user();
        }

        throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
    }

    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenFromRequest();

        if (!$token) {
            return null;
        }

        try {
            $user = $this->validateToken($token);
            if ($user) {
                return $this->user = $user;
            }
        } catch (\Exception $e) {
            Log::warning('Token validation failed: ' . $e->getMessage());
        }

        return null;
    }

    public function validate(array $credentials = [])
    {
        return $this->user() !== null;
    }

    protected function getTokenFromRequest()
    {
        $token = $this->request->bearerToken();

        if (empty($token)) {
            $token = $this->request->getPassword();
        }

        return $token;
    }

    protected function validateToken($token)
    {
        try {
            $hashedToken = hash('sha256', $token);
            $tokenModel = \App\Models\PersonalAccessToken::class;
            
            $tokenInstance = $tokenModel::where('token', $hashedToken)->first();

            if (!$tokenInstance) {
                return null;
            }

            // Use direct method - NO morphTo
            $user = $tokenInstance->getUser();
            if (!$user) {
                return null;
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Token validation exception: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }
}
