<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warranty extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_name',
        'serial_number',
        'warranty_period_months',
        'start_date',
        'end_date',
        'status',
        'order_id',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the order that owns the warranty.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user that owns the warranty.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warranty claims for the warranty.
     */
    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    /**
     * Scope a query to only include active warranties.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE')
                    ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include expired warranties.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'EXPIRED')
                    ->orWhere('end_date', '<', now());
    }

    /**
     * Check if warranty is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE' && $this->end_date >= now();
    }

    /**
     * Check if warranty is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'EXPIRED' || $this->end_date < now();
    }

    /**
     * Get remaining days of warranty.
     */
    public function getRemainingDaysAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->end_date));
    }
}