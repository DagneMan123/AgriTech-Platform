<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'product_id',
        'quantity',
        'unit',
        'pickup_location',
        'delivery_location',
        'pickup_date',
        'delivery_date',
        'vehicle_type',
        'special_handling',
        'handling_instructions',
        'estimated_cost',
        'contact_person',
        'contact_phone',
        'status',
        'assigned_transporter_id',
        'assigned_vehicle_id',
    ];

    protected $casts = [
        'special_handling' => 'boolean',
        'quantity' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'pickup_date' => 'date',
        'delivery_date' => 'date',
    ];

    protected $appends = ['status_label'];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transporter()
    {
        return $this->belongsTo(Transporter::class, 'assigned_transporter_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'assigned' => 'Assigned',
            'in_transit' => 'In Transit',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
