<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    protected $fillable = ['name', 'token', 'abilities'];

    /**
     * Get the tokenable model that the token belongs to.
     */
    public function tokenable()
    {
        return $this->morphTo();
    }
}
