<?php
// app/Models/Review.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewable_id',
        'reviewable_type',
        'rating',
        'comment',
        'images',
        'is_verified'
    ];

    protected $casts = [
        'images' => 'array',
        'is_verified' => 'boolean',
        'rating' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reviewable()
    {
        return $this->morphTo();
    }

    public function getRatingStarsAttribute()
    {
        return str_repeat('⭐', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getIsPositiveAttribute()
    {
        return $this->rating >= 3;
    }

    public function getIsNegativeAttribute()
    {
        return $this->rating < 3;
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images && count($this->images) > 0
            ? asset('storage/' . $this->images[0])
            : null;
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
    public function scopeHighRating($query)
    {
        return $query->where('rating', '>=', 4);
    }
    public function scopeLowRating($query)
    {
        return $query->where('rating', '<=', 2);
    }
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
