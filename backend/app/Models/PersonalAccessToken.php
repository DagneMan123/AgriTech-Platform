<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';
    protected $fillable = ['name', 'token', 'abilities', 'tokenable_type', 'tokenable_id', 'created_at'];
    public $timestamps = true;

    public function tokenable()
    {
        return $this->morphTo();
    }
}
