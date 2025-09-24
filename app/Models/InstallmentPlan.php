<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'total_amount',
        'down_payment',
        'monthly_payment',
        'payment_duration_months',
        'status',
        'start_date',
        'end_date',
        'user_id',
        'solar_system_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'down_payment' => 'decimal:2',
            'monthly_payment' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the installment plan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the solar system for the installment plan.
     */
    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * Get the payments for the installment plan.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope a query to only include completed plans.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    /**
     * Get the remaining balance.
     */
    public function getRemainingBalanceAttribute(): float
    {
        $paidAmount = $this->payments()->where('status', 'PAID')->sum('amount');
        return max(0, $this->total_amount - $this->down_payment - $paidAmount);
    }

    /**
     * Get the total paid amount including down payment.
     */
    public function getTotalPaidAttribute(): float
    {
        $monthlyPaid = $this->payments()->where('status', 'PAID')->sum('amount');
        return $this->down_payment + $monthlyPaid;
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentageAttribute(): int
    {
        if ($this->total_amount <= 0) return 0;
        
        return min(100, (int) round(($this->total_paid / $this->total_amount) * 100));
    }
}