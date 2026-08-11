<?php
// app/Models/Consultation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'expert_id',
        'question',
        'answer',
        'status',
        'category',
        'images',
        'is_public',
        'answered_at'
    ];

    protected $casts = [
        'images' => 'array',
        'is_public' => 'boolean',
        'answered_at' => 'datetime'
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
    public function messages()
    {
        return $this->hasMany(ConsultationMessage::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'answered' => 'Answered',
            'closed' => 'Closed'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'answered' => 'bg-green-100 text-green-800',
            'closed' => 'bg-gray-100 text-gray-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getResponseTimeAttribute()
    {
        if ($this->answered_at && $this->created_at) {
            return $this->created_at->diffInHours($this->answered_at);
        }
        return null;
    }
}
