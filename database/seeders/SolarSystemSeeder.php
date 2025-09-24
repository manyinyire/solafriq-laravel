<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SolarSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systems = [
            [
                'name' => 'Starter Home 1.5kW',
                'description' => 'Perfect for basic home needs, powering lights, electronics, and small appliances. This system is ideal for small families or individuals looking to reduce their electricity bills while maintaining essential power supply.',
                'short_description' => 'Perfect for basic home needs, powering lights, electronics, and small appliances.',
                'capacity' => 1.5,
                'price' => 3500.00,
                'original_price' => 4000.00,
                'installment_price' => 150.00,
                'installment_months' => 24,
                'image_url' => '/placeholder.svg',
                'gallery_images' => ['/placeholder.svg'],
                'use_case' => 'Residential',
                'gradient_colors' => 'linear-gradient(135deg, #f97316 0%, #f59e0b 100%)',
                'is_popular' => true,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Family Power 3kW',
                'description' => 'Ideal for medium-sized families, supporting refrigerators and other larger appliances. This comprehensive system ensures your family never runs out of power for essential household needs.',
                'short_description' => 'Ideal for medium-sized families, supporting refrigerators and other larger appliances.',
                'capacity' => 3.0,
                'price' => 6800.00,
                'original_price' => 7500.00,
                'installment_price' => 280.00,
                'installment_months' => 24,
                'image_url' => '/placeholder.svg',
                'gallery_images' => ['/placeholder.svg'],
                'use_case' => 'Residential',
                'gradient_colors' => 'linear-gradient(135deg, #4f46e5 0%, #818cf8 100%)',
                'is_popular' => true,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Business Pro 5kW',
                'description' => 'A robust solution for small businesses, ensuring uninterrupted operations. This system can handle multiple appliances, computers, and equipment essential for business continuity.',
                'short_description' => 'A robust solution for small businesses, ensuring uninterrupted operations.',
                'capacity' => 5.0,
                'price' => 11500.00,
                'original_price' => 12500.00,
                'installment_price' => 475.00,
                'installment_months' => 24,
                'image_url' => '/placeholder.svg',
                'gallery_images' => ['/placeholder.svg'],
                'use_case' => 'Commercial',
                'gradient_colors' => 'linear-gradient(135deg, #10b981 0%, #34d399 100%)',
                'is_popular' => false,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($systems as $systemData) {
            $system = \App\Models\SolarSystem::create($systemData);

            // Add features for each system
            $features = [
                [
                    'feature_name' => 'High Efficiency Solar Panels',
                    'feature_value' => 'Premium monocrystalline solar panels with 20+ year warranty',
                    'sort_order' => 1,
                ],
                [
                    'feature_name' => 'Smart Inverter Technology',
                    'feature_value' => 'Advanced MPPT technology for maximum power generation',
                    'sort_order' => 2,
                ],
                [
                    'feature_name' => 'Battery Backup System',
                    'feature_value' => 'Lithium-ion batteries for reliable energy storage',
                    'sort_order' => 3,
                ],
                [
                    'feature_name' => '24/7 Monitoring',
                    'feature_value' => 'Real-time system monitoring via mobile app',
                    'sort_order' => 4,
                ],
            ];

            foreach ($features as $featureData) {
                $system->features()->create($featureData);
            }

            // Add products included in each system
            $products = [
                [
                    'product_name' => 'Solar Panels',
                    'product_description' => '320W Monocrystalline panels',
                    'quantity' => $system->capacity > 3 ? 16 : ($system->capacity > 1.5 ? 10 : 6),
                    'sort_order' => 1,
                ],
                [
                    'product_name' => 'Inverter',
                    'product_description' => 'Pure sine wave hybrid inverter',
                    'quantity' => 1,
                    'sort_order' => 2,
                ],
                [
                    'product_name' => 'Battery Bank',
                    'product_description' => 'Deep cycle lithium-ion batteries',
                    'quantity' => $system->capacity > 3 ? 4 : ($system->capacity > 1.5 ? 2 : 1),
                    'sort_order' => 3,
                ],
                [
                    'product_name' => 'Charge Controller',
                    'product_description' => 'MPPT solar charge controller',
                    'quantity' => 1,
                    'sort_order' => 4,
                ],
                [
                    'product_name' => 'Installation Kit',
                    'product_description' => 'Mounting hardware and cables',
                    'quantity' => 1,
                    'sort_order' => 5,
                ],
            ];

            foreach ($products as $productData) {
                $system->products()->create($productData);
            }

            // Add specifications
            $specifications = [
                [
                    'spec_name' => 'System Capacity',
                    'spec_value' => $system->capacity . ' kW',
                    'sort_order' => 1,
                ],
                [
                    'spec_name' => 'Daily Energy Production',
                    'spec_value' => ($system->capacity * 4) . ' kWh',
                    'sort_order' => 2,
                ],
                [
                    'spec_name' => 'Battery Capacity',
                    'spec_value' => ($system->capacity * 2) . ' kWh',
                    'sort_order' => 3,
                ],
                [
                    'spec_name' => 'Warranty Period',
                    'spec_value' => '25 years',
                    'sort_order' => 4,
                ],
                [
                    'spec_name' => 'Installation Time',
                    'spec_value' => ($system->capacity > 3 ? 2 : 1) . ' days',
                    'sort_order' => 5,
                ],
            ];

            foreach ($specifications as $specData) {
                $system->specifications()->create($specData);
            }
        }
    }
}
