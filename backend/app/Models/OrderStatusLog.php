<?php
// app/Models/OrderStatusLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
        'notes',
        'changed_by'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
