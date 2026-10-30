<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'delivery_addresses';

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'contact_name',
        'contact_role',
        'contact_phone',
        'secondary_phone',
        'region',
        'city',
        'sub_city',
        'woreda_kebele',
        'street_address',
        'landmark',
        'postal_code',
        'is_default',
        'instructions',
        'receiving_hours',
        'capabilities',
        'latitude',
        'longitude',
        'total_deliveries',
        'last_delivery_date'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'capabilities' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'total_deliveries' => 'integer',
        'last_delivery_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
