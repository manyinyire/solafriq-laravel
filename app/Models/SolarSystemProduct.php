<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarSystemProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_name',
        'product_description',
        'quantity',
        'unit_price',
        'sort_order',
        'solar_system_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    /**
     * Get the solar system that owns the product.
     */
    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * Calculate total price for this product.
     */
    public function getTotalPriceAttribute(): ?float
    {
        if ($this->unit_price && $this->quantity) {
            return $this->unit_price * $this->quantity;
        }
        return null;
    }
}