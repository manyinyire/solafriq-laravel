<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'image_url',
        'type',
        'order_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the order that owns the order item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the solar system associated with the order item.
     */
    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }

    /**
     * Calculate total price for this item.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->price * $this->quantity;
    }
}