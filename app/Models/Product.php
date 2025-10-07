<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'brand',
        'model',
        'price',
        'image_url',
        'specifications',
        'stock_quantity',
        'unit',
        'power_rating',
        'capacity',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'power_rating' => 'decimal:2',
            'capacity' => 'decimal:2',
            'specifications' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the display name with brand and model.
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->brand, $this->model, $this->name]);
        return implode(' ', $parts);
    }
}
