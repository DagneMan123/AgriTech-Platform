<?php
// app/Models/Warehouse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'name',
        'address',
        'region',
        'zone',
        'woreda',
        'latitude',
        'longitude',
        'capacity',
        'capacity_unit',
        'status'
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'full' => 'Full'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
