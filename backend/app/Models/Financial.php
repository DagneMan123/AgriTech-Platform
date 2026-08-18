<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    use HasFactory;

    protected $table = 'financial_institutions';

    protected $fillable = [
        'user_id',
        'institution_name',
        'license_number',
        'institution_type',
        'address',
        'services_offered',
        'is_verified',
    ];

    protected $casts = [
        'services_offered' => 'json',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'financial_institution_id');
    }
}
