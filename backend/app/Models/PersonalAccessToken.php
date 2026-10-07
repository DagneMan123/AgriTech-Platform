<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';
    protected $fillable = ['name', 'token', 'abilities', 'tokenable_type', 'tokenable_id', 'created_at', 'last_used_at'];
    public $timestamps = true;
    public $incrementing = true;
    
    // DO NOT LOAD RELATIONSHIPS
    protected $with = [];
    protected $appends = [];
    
    // Override relationships completely to prevent any recursion
    public function tokenable()
    {
        // NEVER call morphTo - it causes infinite recursion
        return null;
    }

    /**
     * Get the User associated with this token without any relationship loading.
     * This is the ONLY safe way to get the user.
     */
    public function getUser()
    {
        if (!$this->tokenable_type || !$this->tokenable_id) {
            return null;
        }

        try {
            // Direct query, no relationships
            $userId = (int)$this->tokenable_id;
            return \App\Models\User::select(['id', 'name', 'email', 'phone', 'role', 'is_active', 'location', 'region'])
                ->withoutEagerLoads()
                ->where('id', $userId)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
