<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\PersonalAccessToken;

trait HasApiTokens
{
    public function createToken($name, $abilities = ['*'])
    {
        $plainToken = Str::random(80);
        $hashedToken = hash('sha256', $plainToken);
        
        $token = $this->tokens()->create([
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
    
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function currentAccessToken()
    {
        $token = request()->bearerToken();
        if (!$token) {
            return null;
        }
        
        $hashedToken = hash('sha256', $token);
        return $this->tokens()->where('token', $hashedToken)->first();
    }
}
