<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_number',
        'buyer_id',
        'farmer_id',
        'title',
        'crop_type',
        'variety',
        'quantity',
        'unit',
        'unit_price',
        'total_amount',
        'advance_payment',
        'currency',
        'start_date',
        'delivery_date',
        'delivery_location',
        'quality_specifications',
        'status',
        'payment_terms',
        'terms_and_conditions',
        'farmer_notes',
        'signed_by_buyer_at',
        'signed_by_farmer_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'start_date' => 'date',
        'delivery_date' => 'date',
        'signed_by_buyer_at' => 'datetime',
        'signed_by_farmer_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($contract) {
            if (empty($contract->contract_number)) {
                $contract->contract_number = 'CON-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
            if (empty($contract->signed_by_buyer_at)) {
                $contract->signed_by_buyer_at = now();
            }
        });
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }

    public function milestones()
    {
        return $this->hasMany(ContractMilestone::class);
    }
}
