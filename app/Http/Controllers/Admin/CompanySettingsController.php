<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class CompanySettingsController extends Controller
{
    protected $imageService;

    public function __construct(ImageOptimizationService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display all company settings
     */
    public function index(): JsonResponse
    {
        try {
            $settings = CompanySetting::getAll();

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch company settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update company settings
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_name' => 'sometimes|string|max:255',
                'company_email' => 'sometimes|email|max:255',
                'company_phone' => 'sometimes|string|max:50',
                'company_address' => 'sometimes|string|max:1000',
                'company_logo' => 'sometimes|image|mimes:jpeg,jpg,png,svg|max:2048',
                'default_currency' => 'sometimes|string|size:3',
                'currency_symbol' => 'sometimes|string|max:5',
                'tax_rate' => 'sometimes|numeric|min:0|max:100',
                'installation_fee' => 'sometimes|numeric|min:0',
                'warranty_period_months' => 'sometimes|integer|min:1|max:120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $updated = [];

            foreach ($request->all() as $key => $value) {
                if ($key === 'company_logo' && $request->hasFile('company_logo')) {
                    // Handle file upload with optimization
                    $file = $request->file('company_logo');

                    // Delete old logo if it exists
                    $oldLogo = CompanySetting::where('key', 'company_logo')->first();
                    if ($oldLogo && $oldLogo->value && !str_contains($oldLogo->value, '/images/solafriq-logo')) {
                        $this->imageService->deleteImage($oldLogo->value);
                    }

                    // Upload and optimize logo
                    $logoPath = $this->imageService->uploadLogo($file);
                    
                    if ($logoPath) {
                        $setting = CompanySetting::set(
                            $key, 
                            $logoPath, 
                            'file', 
                            true, 
                            'Company logo image',
                            'Company',
                            'Company Logo',
                            5
                        );
                        $updated[$key] = CompanySetting::castValue($setting->value, $setting->type);
                    }
                } elseif ($key !== 'company_logo') {
                    // Handle other settings
                    $type = $this->getSettingType($key);
                    $isPublic = $this->isPublicSetting($key);
                    $description = $this->getSettingDescription($key);
                    $group = $this->getSettingGroup($key);
                    $displayName = $this->getSettingDisplayName($key);
                    $order = $this->getSettingOrder($key);

                    $setting = CompanySetting::set(
                        $key, 
                        $value, 
                        $type, 
                        $isPublic, 
                        $description,
                        $group,
                        $displayName,
                        $order
                    );
                    $updated[$key] = CompanySetting::castValue($setting->value, $setting->type);
                }
            }

            // Clear ALL caches
            Cache::flush();
            
            // Also specifically clear company settings caches
            Cache::forget('company_settings_public');
            Cache::forget('company_settings_all');
            foreach ($request->all() as $key => $value) {
                Cache::forget("company_setting_{$key}");
            }

            // Get fresh settings to return
            $freshSettings = CompanySetting::getAll();

            return response()->json([
                'success' => true,
                'message' => 'Company settings updated successfully',
                'data' => $freshSettings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update company settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get public company settings (for frontend use)
     */
    public function getPublic(): JsonResponse
    {
        try {
            $settings = CompanySetting::getPublic();

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch public settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults(): JsonResponse
    {
        try {
            // Delete all existing settings
            CompanySetting::truncate();

            // Initialize defaults
            CompanySetting::initializeDefaults();

            // Clear all cached company settings
            Cache::forget('company_settings_public');
            Cache::forget('company_settings_all');
            // Also clear individual setting caches
            foreach ($request->all() as $key => $value) {
                Cache::forget("company_setting_{$key}");
            }

            $settings = CompanySetting::getAll();

            return response()->json([
                'success' => true,
                'message' => 'Company settings reset to defaults successfully',
                'data' => $settings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset company settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export settings as JSON
     */
    public function export(): JsonResponse
    {
        try {
            $settings = CompanySetting::getAll();

            return response()->json([
                'success' => true,
                'filename' => 'company-settings-' . date('Y-m-d-H-i-s') . '.json',
                'data' => $settings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export company settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import settings from JSON
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $imported = 0;
            foreach ($request->input('settings') as $key => $config) {
                if (is_array($config) && isset($config['value'], $config['type'])) {
                    CompanySetting::set(
                        $key,
                        $config['value'],
                        $config['type'],
                        $config['is_public'] ?? false,
                        $config['description'] ?? null
                    );
                    $imported++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} settings",
                'data' => CompanySetting::getAll(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import company settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get setting type based on key
     */
    private function getSettingType(string $key): string
    {
        $types = [
            'company_name' => 'string',
            'company_email' => 'string',
            'company_phone' => 'string',
            'company_address' => 'text',
            'company_logo' => 'file',
            'default_currency' => 'string',
            'currency_symbol' => 'string',
            'tax_rate' => 'float',
            'installation_fee' => 'float',
            'warranty_period_months' => 'integer',
        ];

        return $types[$key] ?? 'string';
    }

    /**
     * Check if setting should be public
     */
    private function isPublicSetting(string $key): bool
    {
        $publicSettings = [
            'company_name',
            'company_email',
            'company_phone',
            'company_address',
            'company_logo',
            'default_currency',
            'currency_symbol',
            'warranty_period_months',
        ];

        return in_array($key, $publicSettings);
    }

    /**
     * Get setting description
     */
    private function getSettingDescription(string $key): string
    {
        $descriptions = [
            'company_name' => 'Company name displayed throughout the application',
            'company_email' => 'Primary company email address',
            'company_phone' => 'Company phone number',
            'company_address' => 'Company physical address',
            'company_logo' => 'Company logo image',
            'default_currency' => 'Default currency for pricing and transactions',
            'currency_symbol' => 'Currency symbol to display',
            'tax_rate' => 'Default tax rate percentage',
            'installation_fee' => 'Default installation fee in base currency',
            'warranty_period_months' => 'Default warranty period in months',
        ];

        return $descriptions[$key] ?? '';
    }

    /**
     * Get setting group
     */
    private function getSettingGroup(string $key): string
    {
        $groups = [
            'company_name' => 'Company',
            'company_email' => 'Company',
            'company_phone' => 'Company',
            'company_address' => 'Company',
            'company_logo' => 'Company',
            'default_currency' => 'Financial',
            'currency_symbol' => 'Financial',
            'tax_rate' => 'Financial',
            'installation_fee' => 'Financial',
            'warranty_period_months' => 'Product',
        ];

        return $groups[$key] ?? 'General';
    }

    /**
     * Get setting display name
     */
    private function getSettingDisplayName(string $key): string
    {
        $displayNames = [
            'company_name' => 'Company Name',
            'company_email' => 'Company Email',
            'company_phone' => 'Company Phone',
            'company_address' => 'Company Address',
            'company_logo' => 'Company Logo',
            'default_currency' => 'Default Currency',
            'currency_symbol' => 'Currency Symbol',
            'tax_rate' => 'Tax Rate (%)',
            'installation_fee' => 'Installation Fee',
            'warranty_period_months' => 'Warranty Period (Months)',
        ];

        return $displayNames[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    /**
     * Get setting order
     */
    private function getSettingOrder(string $key): int
    {
        $orders = [
            'company_name' => 1,
            'company_email' => 2,
            'company_phone' => 3,
            'company_address' => 4,
            'company_logo' => 5,
            'default_currency' => 10,
            'currency_symbol' => 11,
            'tax_rate' => 12,
            'installation_fee' => 13,
            'warranty_period_months' => 20,
        ];

        return $orders[$key] ?? 999;
    }
}