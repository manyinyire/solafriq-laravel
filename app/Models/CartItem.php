<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'solar_system_id',
        'product_id',
        'item_type',
        'custom_system_name',
        'quantity',
        'price',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getItemAttribute()
    {
        if ($this->item_type === 'product') {
            return $this->product;
        }
        return $this->solarSystem;
    }

    public function getItemNameAttribute(): string
    {
        if ($this->item_type === 'custom_component') {
            return $this->product ? $this->product->name . ' (Custom)' : 'Unknown Custom Component';
        }
        if ($this->item_type === 'product') {
            return $this->product ? $this->product->name : 'Unknown Product';
        }
        return $this->solarSystem ? $this->solarSystem->name : 'Unknown System';
    }
}
