<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';
    
    protected $fillable = ['name', 'token', 'abilities', 'tokenable_type', 'tokenable_id'];

    /**
     * Get the tokenable model that the token belongs to.
     */
    public function tokenable()
    {
        return $this->morphTo();
    }
}
