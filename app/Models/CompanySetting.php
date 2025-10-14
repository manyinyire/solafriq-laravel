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
        'group',
        'display_name',
        'value',
        'type',
        'description',
        'order',
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
    public static function set(
        string $key, 
        $value, 
        string $type = 'string', 
        bool $isPublic = false, 
        string $description = null,
        string $group = null,
        string $displayName = null,
        int $order = 0
    ) {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::prepareValue($value, $type),
                'type' => $type,
                'is_public' => $isPublic,
                'description' => $description,
                'group' => $group,
                'display_name' => $displayName,
                'order' => $order,
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
                    // Store in public directory instead of storage for better compatibility
                    $filename = time() . '_' . $value->getClientOriginalName();
                    $value->move(public_path('uploads/company-settings'), $filename);
                    return '/uploads/company-settings/' . $filename;
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
                'group' => 'Company',
                'display_name' => 'Company Name',
                'value' => 'SolaFriq',
                'type' => 'string',
                'description' => 'Company name displayed throughout the application',
                'is_public' => true,
                'order' => 1,
            ],
            'company_email' => [
                'group' => 'Company',
                'display_name' => 'Company Email',
                'value' => 'info@solafriq.com',
                'type' => 'string',
                'description' => 'Primary company email address',
                'is_public' => true,
                'order' => 2,
            ],
            'company_phone' => [
                'group' => 'Company',
                'display_name' => 'Company Phone',
                'value' => '+1-XXX-XXX-XXXX',
                'type' => 'string',
                'description' => 'Company phone number',
                'is_public' => true,
                'order' => 3,
            ],
            'company_address' => [
                'group' => 'Company',
                'display_name' => 'Company Address',
                'value' => 'New York, USA',
                'type' => 'text',
                'description' => 'Company physical address',
                'is_public' => true,
                'order' => 4,
            ],
            'company_logo' => [
                'group' => 'Company',
                'display_name' => 'Company Logo',
                'value' => '/images/solafriq-logo.svg',
                'type' => 'file',
                'description' => 'Company logo image',
                'is_public' => true,
                'order' => 5,
            ],
            'default_currency' => [
                'group' => 'Financial',
                'display_name' => 'Default Currency',
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency for pricing and transactions',
                'is_public' => true,
                'order' => 10,
            ],
            'currency_symbol' => [
                'group' => 'Financial',
                'display_name' => 'Currency Symbol',
                'value' => '$',
                'type' => 'string',
                'description' => 'Currency symbol to display',
                'is_public' => true,
                'order' => 11,
            ],
            'tax_rate' => [
                'group' => 'Financial',
                'display_name' => 'Tax Rate (%)',
                'value' => '8.25',
                'type' => 'float',
                'description' => 'Default tax rate percentage',
                'is_public' => false,
                'order' => 12,
            ],
            'installation_fee' => [
                'group' => 'Financial',
                'display_name' => 'Installation Fee',
                'value' => '500',
                'type' => 'float',
                'description' => 'Default installation fee in base currency',
                'is_public' => false,
                'order' => 13,
            ],
            'warranty_period_months' => [
                'group' => 'Product',
                'display_name' => 'Warranty Period (Months)',
                'value' => '24',
                'type' => 'integer',
                'description' => 'Default warranty period in months',
                'is_public' => true,
                'order' => 20,
            ],
        ];

        foreach ($defaults as $key => $config) {
            if (!static::where('key', $key)->exists()) {
                static::create(array_merge(['key' => $key], $config));
            }
        }
    }
}