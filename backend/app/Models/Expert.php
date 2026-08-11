<?php
// app/Models/Expert.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expert extends Model
{
    use HasFactory;

    protected $table = 'expert_profiles';

    protected $fillable = [
        'user_id',
        'specialization',
        'qualifications',
        'years_experience',
        'expertise_area',
        'bio',
        'is_verified',
        'is_available',
        'consultation_fee'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_available' => 'boolean',
        'consultation_fee' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getTotalConsultationsAttribute()
    {
        return $this->consultations()->count();
    }

    public function getAnsweredConsultationsAttribute()
    {
        return $this->consultations()->where('status', 'answered')->count();
    }
}
