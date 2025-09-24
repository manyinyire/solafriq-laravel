<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyClaim extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'claim_description',
        'status',
        'resolution_details',
        'warranty_id',
        'user_id',
    ];

    /**
     * Get the warranty that owns the claim.
     */
    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    /**
     * Get the user that owns the claim.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include pending claims.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope a query to only include approved claims.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Scope a query to only include rejected claims.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'REJECTED');
    }

    /**
     * Check if claim is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if claim is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Check if claim is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    /**
     * Approve the claim.
     */
    public function approve(string $resolutionDetails = null): void
    {
        $this->update([
            'status' => 'APPROVED',
            'resolution_details' => $resolutionDetails,
        ]);
    }

    /**
     * Reject the claim.
     */
    public function reject(string $resolutionDetails = null): void
    {
        $this->update([
            'status' => 'REJECTED',
            'resolution_details' => $resolutionDetails,
        ]);
    }
}