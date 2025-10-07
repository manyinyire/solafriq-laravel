<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are updated
        static::saved(function ($setting) {
            Cache::forget("company_setting_{$setting->key}");
            Cache::forget('company_settings_all');
            Cache::forget('company_settings_public');
        });

        static::deleted(function ($setting) {
            Cache::forget("company_setting_{$setting->key}");
            Cache::forget('company_settings_all');
            Cache::forget('company_settings_public');
        });
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("company_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value, string $type = 'string', bool $isPublic = false, string $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::prepareValue($value, $type),
                'type' => $type,
                'is_public' => $isPublic,
                'description' => $description,
            ]
        );

        return $setting;
    }

    /**
     * Get all public settings (for frontend)
     */
    public static function getPublic()
    {
        return Cache::remember('company_settings_public', 3600, function () {
            return static::where('is_public', true)
                ->get()
                ->pluck('value', 'key')
                ->map(function ($value, $key) {
                    $setting = static::where('key', $key)->first();
                    return static::castValue($value, $setting->type);
                });
        });
    }

    /**
     * Get all settings (for admin)
     */
    public static function getAll()
    {
        return Cache::remember('company_settings_all', 3600, function () {
            return static::all()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => [
                        'value' => static::castValue($setting->value, $setting->type),
                        'type' => $setting->type,
                        'description' => $setting->description,
                        'is_public' => $setting->is_public,
                    ]];
                });
        });
    }

    /**
     * Prepare value for storage based on type
     */
    protected static function prepareValue($value, string $type)
    {
        switch ($type) {
            case 'json':
                return is_string($value) ? $value : json_encode($value);
            case 'boolean':
                return $value ? '1' : '0';
            case 'file':
                // Handle file uploads
                if (is_object($value) && method_exists($value, 'store')) {
                    return $value->store('company-settings', 'public');
                }
                return $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Cast value from storage based on type
     */
    protected static function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'json':
                return json_decode($value, true);
            case 'boolean':
                return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'file':
                if ($value) {
                    // If it's already a full URL (starts with http), return as is
                    if (str_starts_with($value, 'http')) {
                        return $value;
                    }
                    // If it starts with /storage/, return as is
                    if (str_starts_with($value, '/storage/')) {
                        return $value;
                    }
                    // If it starts with /, it's a public path, return as is
                    if (str_starts_with($value, '/')) {
                        return $value;
                    }
                    // Otherwise, it's a storage path, so add storage URL
                    return '/storage/' . $value;
                }
                return null;
            default:
                return $value;
        }
    }

    /**
     * Initialize default company settings
     */
    public static function initializeDefaults()
    {
        $defaults = [
            'company_name' => [
                'value' => 'SolaFriq',
                'type' => 'string',
                'description' => 'Company name displayed throughout the application',
                'is_public' => true,
            ],
            'company_email' => [
                'value' => 'info@solafriq.com',
                'type' => 'string',
                'description' => 'Primary company email address',
                'is_public' => true,
            ],
            'company_phone' => [
                'value' => '+1-XXX-XXX-XXXX',
                'type' => 'string',
                'description' => 'Company phone number',
                'is_public' => true,
            ],
            'company_address' => [
                'value' => 'New York, USA',
                'type' => 'text',
                'description' => 'Company physical address',
                'is_public' => true,
            ],
            'company_logo' => [
                'value' => '/images/solafriq-logo.png',
                'type' => 'file',
                'description' => 'Company logo image',
                'is_public' => true,
            ],
            'default_currency' => [
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency for pricing and transactions',
                'is_public' => true,
            ],
            'currency_symbol' => [
                'value' => '$',
                'type' => 'string',
                'description' => 'Currency symbol to display',
                'is_public' => true,
            ],
            'tax_rate' => [
                'value' => '8.25',
                'type' => 'float',
                'description' => 'Default tax rate percentage',
                'is_public' => false,
            ],
            'installation_fee' => [
                'value' => '500',
                'type' => 'float',
                'description' => 'Default installation fee in base currency',
                'is_public' => false,
            ],
            'warranty_period_months' => [
                'value' => '24',
                'type' => 'integer',
                'description' => 'Default warranty period in months',
                'is_public' => true,
            ],
        ];

        foreach ($defaults as $key => $config) {
            if (!static::where('key', $key)->exists()) {
                static::create(array_merge(['key' => $key], $config));
            }
        }
    }
}