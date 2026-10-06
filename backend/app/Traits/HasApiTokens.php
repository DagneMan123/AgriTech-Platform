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
        
        $this->tokens()->create([
            'name' => $name,
            'token' => $hashedToken,
            'abilities' => json_encode($abilities),
        ]);
        
        return new class($plainToken) {
            public $plainTextToken;
            
            public function __construct($token)
            {
                $this->plainTextToken = $token;
            }
        };
    }
    
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function currentAccessToken()
    {
        return $this->tokens()->latest()->first();
    }
}
