<?php
// app/Models/CooperativeMember.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CooperativeMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'cooperative_id',
        'farmer_id',
        'status',
        'joined_at',
        'left_at',
        'notes'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime'
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }
    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending' => 'Pending'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
