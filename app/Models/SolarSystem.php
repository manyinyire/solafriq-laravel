<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolarSystem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'short_description',
        'capacity',
        'price',
        'original_price',
        'installment_price',
        'installment_months',
        'image_url',
        'gallery_images',
        'use_case',
        'gradient_colors',
        'is_popular',
        'is_featured',
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
            'original_price' => 'decimal:2',
            'installment_price' => 'decimal:2',
            'gallery_images' => 'array',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the features for the solar system.
     */
    public function features(): HasMany
    {
        return $this->hasMany(SolarSystemFeature::class)->orderBy('sort_order');
    }

    /**
     * Get the products for the solar system.
     */
    public function products(): HasMany
    {
        return $this->hasMany(SolarSystemProduct::class)->orderBy('sort_order');
    }

    /**
     * Get the specifications for the solar system.
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(SolarSystemSpecification::class)->orderBy('sort_order');
    }


    /**
     * Scope a query to only include active systems.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured systems.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include popular systems.
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Calculate savings if original price exists
     */
    public function getSavingsAttribute(): ?float
    {
        if ($this->original_price && $this->price) {
            return $this->original_price - $this->price;
        }
        return null;
    }

    /**
     * Calculate savings percentage if original price exists
     */
    public function getSavingsPercentageAttribute(): ?int
    {
        if ($this->original_price && $this->price && $this->original_price > 0) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }
        return null;
    }
}