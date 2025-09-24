<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SolarSystem;
use App\Models\SolarSystemFeature;
use App\Models\SolarSystemProduct;
use App\Models\SolarSystemSpecification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@solafriq.com',
            'password' => Hash::make('admin123'),
            'role' => 'ADMIN',
            'phone' => '+1-800-555-0123',
            'address' => 'New York, USA',
            'email_verified_at' => now(),
        ]);

        // Create sample client users
        $clients = User::factory(10)->client()->create();

        // Create a test client
        $testClient = User::create([
            'name' => 'Test Client',
            'email' => 'client@solafriq.com',
            'password' => Hash::make('client123'),
            'role' => 'CLIENT',
            'phone' => '+1-213-555-0456',
            'address' => 'Los Angeles, California, USA',
            'email_verified_at' => now(),
        ]);

        // Create solar systems with features, products, and specifications
        $systems = [
            [
                'name' => 'SolaFriq Home Starter 1kW',
                'capacity' => '1kW',
                'price' => 450000,
                'features' => [
                    ['name' => 'Solar Panels', 'value' => '2 x 500W Monocrystalline'],
                    ['name' => 'Battery', 'value' => '100Ah Lithium Battery'],
                    ['name' => 'Inverter', 'value' => '1000W Pure Sine Wave'],
                    ['name' => 'Warranty', 'value' => '2 Years Full Warranty'],
                ],
                'products' => [
                    ['name' => 'Monocrystalline Solar Panel', 'description' => '500W High Efficiency Panel', 'quantity' => 2, 'price' => 70000],
                    ['name' => 'Lithium Battery', 'description' => '100Ah Deep Cycle Battery', 'quantity' => 1, 'price' => 180000],
                    ['name' => 'Pure Sine Wave Inverter', 'description' => '1000W Inverter', 'quantity' => 1, 'price' => 85000],
                    ['name' => 'Charge Controller', 'description' => '30A MPPT Controller', 'quantity' => 1, 'price' => 35000],
                    ['name' => 'Installation Kit', 'description' => 'Mounting, Wiring & Accessories', 'quantity' => 1, 'price' => 80000],
                ],
                'specifications' => [
                    ['name' => 'System Voltage', 'value' => '12V DC', 'category' => 'Electrical'],
                    ['name' => 'Daily Energy Output', 'value' => '4-6 kWh', 'category' => 'Performance'],
                    ['name' => 'Backup Time', 'value' => '6-8 hours', 'category' => 'Performance'],
                    ['name' => 'Installation Time', 'value' => '1-2 days', 'category' => 'Installation'],
                ],
            ],
            [
                'name' => 'SolaFriq Home Essential 3kW',
                'capacity' => '3kW',
                'price' => 980000,
                'features' => [
                    ['name' => 'Solar Panels', 'value' => '6 x 500W Monocrystalline'],
                    ['name' => 'Battery Bank', 'value' => '2 x 200Ah Lithium Batteries'],
                    ['name' => 'Inverter', 'value' => '3000W Pure Sine Wave'],
                    ['name' => 'Smart Monitoring', 'value' => 'Mobile App Integration'],
                ],
                'products' => [
                    ['name' => 'Monocrystalline Solar Panel', 'description' => '500W High Efficiency Panel', 'quantity' => 6, 'price' => 70000],
                    ['name' => 'Lithium Battery', 'description' => '200Ah Deep Cycle Battery', 'quantity' => 2, 'price' => 320000],
                    ['name' => 'Pure Sine Wave Inverter', 'description' => '3000W Inverter', 'quantity' => 1, 'price' => 200000],
                    ['name' => 'MPPT Charge Controller', 'description' => '60A MPPT Controller', 'quantity' => 1, 'price' => 65000],
                ],
                'specifications' => [
                    ['name' => 'System Voltage', 'value' => '24V DC', 'category' => 'Electrical'],
                    ['name' => 'Daily Energy Output', 'value' => '12-18 kWh', 'category' => 'Performance'],
                    ['name' => 'Backup Time', 'value' => '12-16 hours', 'category' => 'Performance'],
                    ['name' => 'Load Capacity', 'value' => 'Medium homes, small offices', 'category' => 'Capacity'],
                ],
            ],
            [
                'name' => 'SolaFriq Commercial Pro 10kW',
                'capacity' => '10kW',
                'price' => 2850000,
                'features' => [
                    ['name' => 'Solar Array', 'value' => '20 x 500W Panels'],
                    ['name' => 'Battery System', 'value' => '8 x 200Ah Lithium Bank'],
                    ['name' => 'Hybrid Inverter', 'value' => '10kW Three-Phase'],
                    ['name' => 'Grid Integration', 'value' => 'Net Metering Ready'],
                ],
                'products' => [
                    ['name' => 'Commercial Solar Panel', 'description' => '500W Tier-1 Panel', 'quantity' => 20, 'price' => 65000],
                    ['name' => 'Lithium Battery Bank', 'description' => '200Ah Commercial Grade', 'quantity' => 8, 'price' => 320000],
                    ['name' => 'Hybrid Inverter', 'description' => '10kW Three-Phase', 'quantity' => 1, 'price' => 580000],
                ],
                'specifications' => [
                    ['name' => 'System Voltage', 'value' => '48V DC', 'category' => 'Electrical'],
                    ['name' => 'Daily Energy Output', 'value' => '40-60 kWh', 'category' => 'Performance'],
                    ['name' => 'Grid Integration', 'value' => 'Hybrid On/Off Grid', 'category' => 'Features'],
                    ['name' => 'Suitable For', 'value' => 'Large homes, offices, shops', 'category' => 'Application'],
                ],
            ]
        ];

        foreach ($systems as $systemData) {
            $system = SolarSystem::create([
                'name' => $systemData['name'],
                'description' => "Complete solar energy solution perfect for residential and commercial applications. This system includes high-quality components with professional installation and 2-year warranty.",
                'short_description' => "Complete " . $systemData['capacity'] . " solar system with battery backup and professional installation.",
                'capacity' => $systemData['capacity'],
                'price' => $systemData['price'],
                'original_price' => $systemData['price'] * 1.2,
                'installment_price' => $systemData['price'] * 1.15,
                'installment_months' => 12,
                'image_url' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=600&fit=crop',
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1497440001374-f26997328c1b?w=800&h=600&fit=crop',
                ],
                'use_case' => 'Perfect for ' . ($systemData['capacity'] === '1kW' ? 'small homes' : ($systemData['capacity'] === '3kW' ? 'medium homes' : 'large properties')),
                'gradient_colors' => 'from-orange-400 to-yellow-500',
                'is_popular' => in_array($systemData['capacity'], ['3kW']),
                'is_featured' => in_array($systemData['capacity'], ['1kW', '3kW']),
                'is_active' => true,
                'sort_order' => array_search($systemData['capacity'], ['1kW', '3kW', '10kW']),
            ]);

            // Create features
            foreach ($systemData['features'] as $index => $feature) {
                SolarSystemFeature::create([
                    'solar_system_id' => $system->id,
                    'feature_name' => $feature['name'],
                    'feature_value' => $feature['value'],
                    'sort_order' => $index,
                ]);
            }

            // Create products
            foreach ($systemData['products'] as $index => $product) {
                SolarSystemProduct::create([
                    'solar_system_id' => $system->id,
                    'product_name' => $product['name'],
                    'product_description' => $product['description'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['price'],
                    'sort_order' => $index,
                ]);
            }

            // Create specifications
            foreach ($systemData['specifications'] as $index => $spec) {
                SolarSystemSpecification::create([
                    'solar_system_id' => $system->id,
                    'spec_name' => $spec['name'],
                    'spec_value' => $spec['value'],
                    'spec_category' => $spec['category'],
                    'sort_order' => $index,
                ]);
            }
        }

        // Create additional random solar systems
        SolarSystem::factory(5)
            ->create()
            ->each(function ($system) {
                // Create features for each system
                SolarSystemFeature::create([
                    'solar_system_id' => $system->id,
                    'feature_name' => 'Solar Panels',
                    'feature_value' => fake()->numberBetween(4, 20) . ' x 500W Panels',
                    'sort_order' => 0,
                ]);

                SolarSystemFeature::create([
                    'solar_system_id' => $system->id,
                    'feature_name' => 'Battery Storage',
                    'feature_value' => fake()->numberBetween(200, 800) . 'Ah Battery Bank',
                    'sort_order' => 1,
                ]);

                // Create products
                SolarSystemProduct::create([
                    'solar_system_id' => $system->id,
                    'product_name' => 'Solar Panel',
                    'product_description' => '500W Monocrystalline Panel',
                    'quantity' => fake()->numberBetween(4, 20),
                    'unit_price' => 70000,
                    'sort_order' => 0,
                ]);

                // Create specifications
                SolarSystemSpecification::create([
                    'solar_system_id' => $system->id,
                    'spec_name' => 'System Voltage',
                    'spec_value' => fake()->randomElement(['12V', '24V', '48V']),
                    'spec_category' => 'Electrical',
                    'sort_order' => 0,
                ]);
            });

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin Login: admin@solafriq.com / admin123');
        $this->command->info('Client Login: client@solafriq.com / client123');
    }
}