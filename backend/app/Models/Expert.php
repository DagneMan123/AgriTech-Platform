<?php
// app/Models/Expert.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;

    protected $table = 'experts';

    protected $fillable = [
        'user_id',
        'expert_registration_number',
        'specialization',
        'qualification',
        'institution',
        'years_of_experience',
        'bio',
        'office_address',
        'office_phone',
        'expertise_areas',
        'consultation_fee',
        'total_consultations',
        'average_rating',
        'verification_status',
        'available_for_consultation'
    ];

    protected $casts = [
        'available_for_consultation' => 'boolean',
        'consultation_fee' => 'decimal:2',
        'average_rating' => 'decimal:2'
    ];

    // CRITICAL: Prevent eager loading to avoid infinite recursion
    protected $with = [];
    protected $appends = [];

    /**
     * DISABLED: All relationships blocked to prevent recursion during auth
     * Use direct database queries instead
     */
    public function user() { return null; }
    public function consultations() { return null; }
    public function reviews() { return null; }

    public function getAverageRatingAttribute()
    {
        // Disabled to prevent recursion - use DB query when needed
        return 0;
    }

    public function getTotalConsultationsAttribute()
    {
        // Disabled to prevent recursion - use DB query when needed
        return 0;
    }

    public function getAnsweredConsultationsAttribute()
    {
        // Disabled to prevent recursion - use DB query when needed
        return 0;
    }
}
