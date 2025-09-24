<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolarSystemFeature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'feature_name',
        'feature_value',
        'sort_order',
        'solar_system_id',
    ];

    /**
     * Get the solar system that owns the feature.
     */
    public function solarSystem(): BelongsTo
    {
        return $this->belongsTo(SolarSystem::class);
    }
}