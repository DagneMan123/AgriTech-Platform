<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'due_date',
        'completed_at',
        'status',
        'payout_percentage',
        'payout_amount',
        'payout_status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'payout_percentage' => 'decimal:2',
        'payout_amount' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
