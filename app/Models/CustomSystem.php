<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomSystem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'system_components',
        'calculation_data',
        'estimated_cost',
        'estimated_savings',
        'payback_period',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'system_components' => 'array',
            'calculation_data' => 'array',
            'estimated_cost' => 'decimal:2',
            'estimated_savings' => 'decimal:2',
            'payback_period' => 'decimal:2',
        ];
    }

    /**
     * Get the user that owns the custom system.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the system is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    /**
     * Check if the system is finalized.
     */
    public function isFinalized(): bool
    {
        return $this->status === 'FINALIZED';
    }

    /**
     * Get the total power capacity in watts.
     */
    public function getTotalPowerCapacity(): float
    {
        $totalPower = 0;

        if (isset($this->system_components['solar_panels'])) {
            foreach ($this->system_components['solar_panels'] as $panel) {
                // Assuming we'll get power rating from the related solar system
                $totalPower += ($panel['power_rating'] ?? 0) * ($panel['quantity'] ?? 1);
            }
        }

        return $totalPower;
    }

    /**
     * Get the total battery capacity in kWh.
     */
    public function getTotalBatteryCapacity(): float
    {
        $totalCapacity = 0;

        if (isset($this->system_components['batteries'])) {
            foreach ($this->system_components['batteries'] as $battery) {
                // Assuming we'll get capacity from the related solar system
                $totalCapacity += ($battery['capacity'] ?? 0) * ($battery['quantity'] ?? 1);
            }
        }

        return $totalCapacity;
    }

    /**
     * Get the estimated annual energy production in kWh.
     */
    public function getEstimatedAnnualProduction(): float
    {
        return $this->calculation_data['annual_production'] ?? 0;
    }

    /**
     * Get the estimated monthly savings in USD.
     */
    public function getEstimatedMonthlySavings(): float
    {
        return $this->estimated_savings ? $this->estimated_savings / 12 : 0;
    }
}