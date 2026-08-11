<?php
// app/Models/LoanApplication.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'farmer_id',
        'financial_institution_id',
        'amount',
        'purpose',
        'description',
        'duration_months',
        'status',
        'documents',
        'notes',
        'reviewed_at',
        'reviewed_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'documents' => 'array',
        'reviewed_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($application) {
            $application->application_number = 'APP-' . date('Ymd') . '-' . Str::random(8);
        });
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }
    public function financialInstitution()
    {
        return $this->belongsTo(User::class, 'financial_institution_id');
    }
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'reviewing' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
