<?php
// app/Models/LoanRepayment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'amount',
        'principal',
        'interest',
        'payment_date',
        'payment_method',
        'reference_number',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal' => 'decimal:2',
        'interest' => 'decimal:2',
        'payment_date' => 'date'
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
