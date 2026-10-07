<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\PersonalAccessToken;

trait HasApiTokens
{
    /**
     * Create a new personal access token for the user.
     * NEVER use $this->tokens() - it causes infinite recursion with morphMany
     */
    public function createToken($name, $abilities = ['*'])
    {
        $plainToken = Str::random(80);
        $hashedToken = hash('sha256', $plainToken);
        
        // Create token directly - bypass relationships
        $token = PersonalAccessToken::create([
            'tokenable_type' => self::class,
            'tokenable_id' => $this->id,
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => json_encode($abilities),
        ]);
        
        return new class($plainToken, $token) {
            public $plainTextToken;
            private $token;
            
            public function __construct($plainToken, $token)
            {
                $this->plainTextToken = $plainToken;
                $this->token = $token;
            }
            
            public function accessToken()
            {
                return $this->token;
            }
        };
    }
    
    /**
     * NEVER call this - morphMany causes infinite recursion
     * Only included for compatibility
     */
    public function tokens()
    {
        // Return an empty relationship to prevent issues
        return collect();
    }

    /**
     * Get current access token without relationship loading.
     * This is the ONLY safe way to get the token.
     */
    public function currentAccessToken()
    {
        $token = request()->bearerToken();
        if (!$token) {
            return null;
        }
        
        $hashedToken = hash('sha256', $token);
        return PersonalAccessToken::where('token', $hashedToken)
            ->where('tokenable_id', $this->id)
            ->where('tokenable_type', self::class)
            ->first();
    }
}
