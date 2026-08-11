<?php
// app/Models/Loan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_number',
        'farmer_id',
        'financial_institution_id',
        'amount',
        'interest_rate',
        'duration_months',
        'purpose',
        'description',
        'status',
        'amount_paid',
        'remaining_balance',
        'notes',
        'documents',
        'approved_at',
        'disbursed_at',
        'due_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'documents' => 'array',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
        'due_date' => 'date'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($loan) {
            $loan->loan_number = 'LN-' . date('Ymd') . '-' . Str::random(8);
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
    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'disbursed' => 'Disbursed',
            'active' => 'Active',
            'completed' => 'Completed',
            'defaulted' => 'Defaulted',
            'rejected' => 'Rejected'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'disbursed' => 'bg-purple-100 text-purple-800',
            'active' => 'bg-green-100 text-green-800',
            'completed' => 'bg-green-100 text-green-800',
            'defaulted' => 'bg-red-100 text-red-800',
            'rejected' => 'bg-red-100 text-red-800'
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getAmountFormattedAttribute()
    {
        return number_format($this->amount, 2) . ' ETB';
    }

    public function getRemainingBalanceFormattedAttribute()
    {
        return number_format($this->remaining_balance ?? 0, 2) . ' ETB';
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->amount > 0) {
            return ($this->amount_paid / $this->amount) * 100;
        }
        return 0;
    }
}
