<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class CompanySettingsController extends Controller
{
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
                    // Handle file upload
                    $file = $request->file('company_logo');

                    // Delete old logo if it exists
                    $oldLogo = CompanySetting::where('key', 'company_logo')->first();
                    if ($oldLogo && $oldLogo->value && !str_contains($oldLogo->value, '/images/solafriq-logo')) {
                        // Handle both storage and public paths
                        if (str_starts_with($oldLogo->value, '/storage/')) {
                            $oldPath = str_replace('/storage/', '', $oldLogo->value);
                            if (Storage::disk('public')->exists($oldPath)) {
                                Storage::disk('public')->delete($oldPath);
                            }
                        } elseif (str_starts_with($oldLogo->value, '/uploads/')) {
                            $oldPath = public_path($oldLogo->value);
                            if (file_exists($oldPath)) {
                                unlink($oldPath);
                            }
                        }
                    }

                    $setting = CompanySetting::set($key, $file, 'file', true, 'Company logo image');
                    $updated[$key] = CompanySetting::castValue($setting->value, $setting->type);
                } elseif ($key !== 'company_logo') {
                    // Handle other settings
                    $type = $this->getSettingType($key);
                    $isPublic = $this->isPublicSetting($key);
                    $description = $this->getSettingDescription($key);

                    $setting = CompanySetting::set($key, $value, $type, $isPublic, $description);
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
}