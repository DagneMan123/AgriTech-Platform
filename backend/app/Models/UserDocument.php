<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_name',
        'file_path',
        'file_type',
        'file_size',
        'verification_status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this document
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who verified this document
     */
    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the document URL
     */
    public function getDownloadUrl()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Mark document as verified
     */
    public function markAsVerified($verifiedBy = null)
    {
        $this->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
            'rejection_reason' => null,
        ]);
        return $this;
    }

    /**
     * Reject document
     */
    public function reject($reason, $rejectedBy = null)
    {
        $this->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_by' => $rejectedBy,
        ]);
        return $this;
    }

    /**
     * Check if document is verified
     */
    public function isVerified()
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Scope to get pending documents
     */
    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    /**
     * Scope to get verified documents
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope to get rejected documents
     */
    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }
}
