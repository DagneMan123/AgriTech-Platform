<?php
// app/Models/SupplierLicense.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'license_type',
        'license_number',
        'issue_date',
        'expiry_date',
        'status',
        'documents',
        'notes',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'documents' => 'array',
        'verified_at' => 'datetime'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired' => 'Expired'
        ];
        return $statuses[$this->status] ?? 'Unknown';
    }
}
