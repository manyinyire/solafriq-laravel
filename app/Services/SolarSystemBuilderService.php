<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SolarSystemBuilderService
{
    // Solar constants for calculations
    private const AVERAGE_SUN_HOURS_USA = 5.0; // Peak sun hours per day
    private const BATTERY_DEPTH_OF_DISCHARGE = 0.8; // 80% DOD for lithium
    private const SYSTEM_EFFICIENCY = 0.85; // 85% overall system efficiency
    private const INVERTER_EFFICIENCY = 0.95; // 95% inverter efficiency

    /**
     * Calculate system requirements based on energy needs
     */
    public function calculateSystemRequirements(array $energyNeeds): array
    {
        $dailyEnergyKwh = $energyNeeds['daily_energy_kwh'];
        $backupDays = $energyNeeds['backup_days'] ?? 2;
        $location = $energyNeeds['location'] ?? 'New York';
        $budgetRange = $energyNeeds['budget_range'] ?? null;

        // Adjust sun hours based on location (US-specific)
        $sunHours = $this->getSunHoursForLocation($location);

        // Calculate solar panel requirements
        $solarRequirements = $this->calculateSolarPanelRequirements($dailyEnergyKwh, $sunHours);

        // Calculate battery requirements
        $batteryRequirements = $this->calculateBatteryRequirements($dailyEnergyKwh, $backupDays);

        // Calculate inverter requirements
        $inverterRequirements = $this->calculateInverterRequirements($dailyEnergyKwh, $energyNeeds['peak_load_kw'] ?? null);

        // Calculate installation requirements
        $installationRequirements = $this->calculateInstallationRequirements($solarRequirements, $batteryRequirements);

        // Estimate costs
        $costEstimate = $this->calculateSystemCost($solarRequirements, $batteryRequirements, $inverterRequirements, $installationRequirements);

        // Calculate savings
        $savingsCalculation = $this->calculateSavings($dailyEnergyKwh, $location, $costEstimate['total_cost']);

        return [
            'solar_panels' => $solarRequirements,
            'batteries' => $batteryRequirements,
            'inverter' => $inverterRequirements,
            'installation' => $installationRequirements,
            'cost_estimate' => $costEstimate,
            'savings_analysis' => $savingsCalculation,
            'system_analysis' => $this->generateSystemAnalysis(
                $solarRequirements, 
                $batteryRequirements, 
                $dailyEnergyKwh, 
                $backupDays
            ),
            'recommendations' => $this->generateRecommendations($energyNeeds, $budgetRange)
        ];
    }

    /**
     * Validate a custom system configuration
     */
    public function validateSystemConfiguration(array $components): array
    {
        $errors = [];
        $warnings = [];

        // Validate panel-inverter compatibility
        $panelValidation = $this->validatePanelInverterCompatibility($components);
        if (!$panelValidation['valid']) {
            $errors = array_merge($errors, $panelValidation['errors']);
        }
        $warnings = array_merge($warnings, $panelValidation['warnings']);

        // Validate battery capacity
        $batteryValidation = $this->validateBatteryCapacity($components);
        if (!$batteryValidation['valid']) {
            $errors = array_merge($errors, $batteryValidation['errors']);
        }
        $warnings = array_merge($warnings, $batteryValidation['warnings']);

        // Validate system balance
        $balanceValidation = $this->validateSystemBalance($components);
        if (!$balanceValidation['valid']) {
            $errors = array_merge($errors, $balanceValidation['errors']);
        }
        $warnings = array_merge($warnings, $balanceValidation['warnings']);

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'optimizations' => $this->suggestOptimizations($components),
        ];
    }

    /**
     * Calculate solar panel requirements
     */
    private function calculateSolarPanelRequirements(float $dailyEnergyKwh, float $sunHours): array
    {
        // Account for system losses
        $requiredSolarKw = ($dailyEnergyKwh / $sunHours) / self::SYSTEM_EFFICIENCY;

        // Standard panel sizes (common in USA)
        $panelOptions = [
            ['wattage' => 300, 'price' => 180],
            ['wattage' => 400, 'price' => 220],
            ['wattage' => 500, 'price' => 280],
            ['wattage' => 550, 'price' => 320],
        ];

        $recommendations = [];
        foreach ($panelOptions as $panel) {
            $panelCount = ceil(($requiredSolarKw * 1000) / $panel['wattage']);
            $actualCapacity = ($panelCount * $panel['wattage']) / 1000;
            $totalCost = $panelCount * $panel['price'];

            $recommendations[] = [
                'panel_wattage' => $panel['wattage'],
                'panel_count' => $panelCount,
                'total_capacity_kw' => $actualCapacity,
                'daily_generation_kwh' => $actualCapacity * $sunHours * self::SYSTEM_EFFICIENCY,
                'unit_price' => $panel['price'],
                'total_cost' => $totalCost,
                'cost_per_kw' => $totalCost / $actualCapacity,
            ];
        }

        // Sort by cost effectiveness
        usort($recommendations, fn($a, $b) => $a['cost_per_kw'] <=> $b['cost_per_kw']);

        return [
            'required_capacity_kw' => $requiredSolarKw,
            'recommended_options' => $recommendations,
            'preferred_option' => $recommendations[0] ?? null,
        ];
    }

    /**
     * Calculate battery requirements
     */
    private function calculateBatteryRequirements(float $dailyEnergyKwh, int $backupDays): array
    {
        // Required usable capacity
        $usableCapacityKwh = $dailyEnergyKwh * $backupDays;
        
        // Account for depth of discharge
        $totalCapacityKwh = $usableCapacityKwh / self::BATTERY_DEPTH_OF_DISCHARGE;

        // Battery options (common in USA)
        $batteryOptions = [
            ['type' => 'Lithium', 'voltage' => 12, 'capacity_ah' => 100, 'price' => 720, 'dod' => 0.9, 'cycles' => 6000],
            ['type' => 'Lithium', 'voltage' => 12, 'capacity_ah' => 200, 'price' => 1280, 'dod' => 0.9, 'cycles' => 6000],
            ['type' => 'AGM', 'voltage' => 12, 'capacity_ah' => 200, 'price' => 480, 'dod' => 0.5, 'cycles' => 1000],
            ['type' => 'Gel', 'voltage' => 12, 'capacity_ah' => 200, 'price' => 560, 'dod' => 0.6, 'cycles' => 1200],
        ];

        $recommendations = [];
        foreach ($batteryOptions as $battery) {
            $batteryCapacityKwh = ($battery['voltage'] * $battery['capacity_ah']) / 1000;
            $usablePerBattery = $batteryCapacityKwh * $battery['dod'];
            $requiredCount = ceil($usableCapacityKwh / $usablePerBattery);
            $totalCapacity = $batteryCapacityKwh * $requiredCount;
            $totalUsableCapacity = $totalCapacity * $battery['dod'];
            $totalCost = $requiredCount * $battery['price'];

            $recommendations[] = [
                'battery_type' => $battery['type'],
                'voltage' => $battery['voltage'],
                'capacity_ah' => $battery['capacity_ah'],
                'battery_count' => $requiredCount,
                'total_capacity_kwh' => $totalCapacity,
                'usable_capacity_kwh' => $totalUsableCapacity,
                'backup_time_days' => $totalUsableCapacity / $dailyEnergyKwh,
                'unit_price' => $battery['price'],
                'total_cost' => $totalCost,
                'lifecycle_cycles' => $battery['cycles'],
                'cost_per_kwh' => $totalCost / $totalCapacity,
            ];
        }

        // Sort by cost per kWh
        usort($recommendations, fn($a, $b) => $a['cost_per_kwh'] <=> $b['cost_per_kwh']);

        return [
            'required_usable_capacity_kwh' => $usableCapacityKwh,
            'required_total_capacity_kwh' => $totalCapacityKwh,
            'recommended_options' => $recommendations,
            'preferred_option' => $recommendations[0] ?? null,
        ];
    }

    /**
     * Calculate inverter requirements
     */
    private function calculateInverterRequirements(float $dailyEnergyKwh, ?float $peakLoadKw = null): array
    {
        // Estimate peak load if not provided (typically 30-40% of daily energy in kW)
        $estimatedPeakLoad = $peakLoadKw ?? ($dailyEnergyKwh * 0.35);
        
        // Add 25% safety margin
        $requiredInverterCapacity = $estimatedPeakLoad * 1.25;

        // Inverter options
        $inverterOptions = [
            ['capacity_kw' => 1.0, 'type' => 'Pure Sine Wave', 'price' => 340],
            ['capacity_kw' => 1.5, 'type' => 'Pure Sine Wave', 'price' => 480],
            ['capacity_kw' => 2.0, 'type' => 'Pure Sine Wave', 'price' => 600],
            ['capacity_kw' => 3.0, 'type' => 'Pure Sine Wave', 'price' => 800],
            ['capacity_kw' => 5.0, 'type' => 'Pure Sine Wave', 'price' => 1280],
        ];

        $recommendations = array_filter($inverterOptions, 
            fn($inverter) => $inverter['capacity_kw'] >= $requiredInverterCapacity
        );

        return [
            'required_capacity_kw' => $requiredInverterCapacity,
            'estimated_peak_load_kw' => $estimatedPeakLoad,
            'recommended_options' => array_values($recommendations),
            'preferred_option' => $recommendations[0] ?? end($inverterOptions),
        ];
    }

    /**
     * Calculate installation requirements
     */
    private function calculateInstallationRequirements(array $solarRequirements, array $batteryRequirements): array
    {
        $panelCount = $solarRequirements['preferred_option']['panel_count'] ?? 0;
        $batteryCount = $batteryRequirements['preferred_option']['battery_count'] ?? 0;

        // Installation cost factors
        $baseCost = 400; // Base installation cost
        $panelInstallationCost = $panelCount * 60; // Per panel installation
        $batteryInstallationCost = $batteryCount * 20; // Per battery installation
        $wiringCost = $panelCount * 32; // Wiring per panel
        $mountingCost = $panelCount * 48; // Mounting per panel

        $totalInstallationCost = $baseCost + $panelInstallationCost + 
                               $batteryInstallationCost + $wiringCost + $mountingCost;

        return [
            'base_installation' => $baseCost,
            'panel_installation' => $panelInstallationCost,
            'battery_installation' => $batteryInstallationCost,
            'wiring_cost' => $wiringCost,
            'mounting_cost' => $mountingCost,
            'total_installation_cost' => $totalInstallationCost,
            'estimated_duration_days' => ceil($panelCount / 4) + 1, // Installation time
        ];
    }

    /**
     * Calculate total system cost
     */
    private function calculateSystemCost(array $solar, array $battery, array $inverter, array $installation): array
    {
        $solarCost = $solar['preferred_option']['total_cost'] ?? 0;
        $batteryCost = $battery['preferred_option']['total_cost'] ?? 0;
        $inverterCost = $inverter['preferred_option']['price'] ?? 0;
        $installationCost = $installation['total_installation_cost'];

        $subtotal = $solarCost + $batteryCost + $inverterCost + $installationCost;
        $tax = $subtotal * 0.0825; // 8.25% Sales Tax
        $total = $subtotal + $tax;

        return [
            'solar_panels_cost' => $solarCost,
            'batteries_cost' => $batteryCost,
            'inverter_cost' => $inverterCost,
            'installation_cost' => $installationCost,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total_cost' => $total,
            'cost_per_kwh_capacity' => $total / ($solar['preferred_option']['total_capacity_kw'] ?? 1),
        ];
    }

    /**
     * Calculate savings and ROI
     */
    private function calculateSavings(float $dailyEnergyKwh, string $location, float $systemCost): array
    {
        // Average electricity costs in USA (cents per kWh)
        $electricityRates = [
            'New York' => 0.20, // USD per kWh
            'Los Angeles' => 0.22,
            'Chicago' => 0.13,
            'Houston' => 0.12,
            'Phoenix' => 0.13,
            'Philadelphia' => 0.14,
            'San Antonio' => 0.11,
            'San Diego' => 0.28,
            'Dallas' => 0.12,
            'San Jose' => 0.25,
            'default' => 0.16,
        ];

        $electricityRate = $electricityRates[$location] ?? $electricityRates['default'];
        
        $monthlyElectricityBill = $dailyEnergyKwh * 30 * $electricityRate;
        $annualElectricityBill = $monthlyElectricityBill * 12;

        // Assume 20% grid electricity still needed
        $gridReductionPercentage = 80;
        $annualSavings = $annualElectricityBill * ($gridReductionPercentage / 100);

        $paybackPeriodYears = $systemCost / $annualSavings;
        $twentyYearSavings = ($annualSavings * 20) - $systemCost;

        return [
            'current_monthly_bill' => $monthlyElectricityBill,
            'current_annual_bill' => $annualElectricityBill,
            'annual_savings' => $annualSavings,
            'monthly_savings' => $annualSavings / 12,
            'payback_period_years' => $paybackPeriodYears,
            'twenty_year_savings' => $twentyYearSavings,
            'roi_percentage' => (($twentyYearSavings / $systemCost) * 100),
        ];
    }

    /**
     * Get sun hours for specific location
     */
    private function getSunHoursForLocation(string $location): float
    {
        $sunHours = [
            'New York' => 4.2,
            'Los Angeles' => 6.2,
            'Chicago' => 4.1,
            'Houston' => 4.8,
            'Phoenix' => 6.5,
            'Philadelphia' => 4.3,
            'San Antonio' => 5.2,
            'San Diego' => 6.0,
            'Dallas' => 4.9,
            'San Jose' => 5.8,
        ];

        return $sunHours[$location] ?? self::AVERAGE_SUN_HOURS_USA;
    }

    /**
     * Generate system analysis summary
     */
    private function generateSystemAnalysis(array $solar, array $battery, float $dailyEnergy, int $backupDays): array
    {
        $solarCapacity = $solar['preferred_option']['total_capacity_kw'] ?? 0;
        $batteryCapacity = $battery['preferred_option']['usable_capacity_kwh'] ?? 0;
        $dailyGeneration = $solar['preferred_option']['daily_generation_kwh'] ?? 0;

        return [
            'total_solar_capacity_kw' => $solarCapacity,
            'total_battery_capacity_kwh' => $batteryCapacity,
            'daily_energy_generation_kwh' => $dailyGeneration,
            'backup_duration_days' => $batteryCapacity / $dailyEnergy,
            'energy_independence_percentage' => min(100, ($dailyGeneration / $dailyEnergy) * 100),
            'system_oversizing_percentage' => max(0, (($dailyGeneration - $dailyEnergy) / $dailyEnergy) * 100),
        ];
    }

    // Additional validation and optimization methods would go here...
    private function validatePanelInverterCompatibility(array $components): array
    {
        // Implementation for panel-inverter compatibility validation
        return ['valid' => true, 'errors' => [], 'warnings' => []];
    }

    private function validateBatteryCapacity(array $components): array
    {
        // Implementation for battery capacity validation
        return ['valid' => true, 'errors' => [], 'warnings' => []];
    }

    private function validateSystemBalance(array $components): array
    {
        // Implementation for system balance validation
        return ['valid' => true, 'errors' => [], 'warnings' => []];
    }

    private function suggestOptimizations(array $components): array
    {
        // Implementation for system optimization suggestions
        return [];
    }

    private function generateRecommendations(array $energyNeeds, ?array $budgetRange): array
    {
        // Implementation for generating personalized recommendations
        return [];
    }
}