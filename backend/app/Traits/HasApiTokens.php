<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\PersonalAccessToken;

/**
 * Simple API token trait for authentication
 * Stores tokens in the personal_access_tokens table
 */
trait HasApiTokens
{
    /**
     * Create a new personal access token for the user.
     */
    public function createToken($name, $abilities = ['*'])
    {
        $token = Str::random(80);
        $hashedToken = hash('sha256', $token);
        
        // Store the token in the database
        $this->tokens()->create([
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => json_encode($abilities),
        ]);
        
        return new class($token) {
            public $plainTextToken;
            
            public function __construct($token)
            {
                $this->plainTextToken = $token;
            }
        };
    }
    
    /**
     * Get all tokens for this user (polymorphic relationship).
     */
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    /**
     * Get the current access token being used by the user.
     */
    public function currentAccessToken()
    {
        return $this->tokens()->latest()->first();
    }
}
