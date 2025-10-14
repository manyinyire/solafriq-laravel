<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Solar Panels
            [
                'name' => 'Monocrystalline Solar Panel',
                'description' => 'High-efficiency monocrystalline solar panel with 21% efficiency rating. Perfect for residential installations.',
                'category' => 'SOLAR_PANEL',
                'brand' => 'SunPower',
                'model' => 'SPR-X22-370',
                'price' => 350.00,
                'power_rating' => 370,
                'stock_quantity' => 50,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Polycrystalline Solar Panel',
                'description' => 'Cost-effective polycrystalline panel with good performance in various weather conditions.',
                'category' => 'SOLAR_PANEL',
                'brand' => 'Canadian Solar',
                'model' => 'CS3W-400P',
                'price' => 280.00,
                'power_rating' => 400,
                'stock_quantity' => 75,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Bifacial Solar Panel',
                'description' => 'Advanced bifacial technology captures sunlight from both sides for maximum energy production.',
                'category' => 'SOLAR_PANEL',
                'brand' => 'LONGi',
                'model' => 'LR4-72HBD-450M',
                'price' => 420.00,
                'power_rating' => 450,
                'stock_quantity' => 30,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 3,
            ],

            // Inverters
            [
                'name' => 'String Inverter',
                'description' => 'Reliable string inverter for residential solar systems with 97.5% efficiency.',
                'category' => 'INVERTER',
                'brand' => 'SMA',
                'model' => 'Sunny Boy 5.0',
                'price' => 1200.00,
                'power_rating' => 5000,
                'stock_quantity' => 25,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Hybrid Inverter',
                'description' => 'Advanced hybrid inverter with battery storage capability and smart grid integration.',
                'category' => 'INVERTER',
                'brand' => 'Fronius',
                'model' => 'Primo GEN24 6.0',
                'price' => 1800.00,
                'power_rating' => 6000,
                'stock_quantity' => 15,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Microinverter',
                'description' => 'Panel-level optimization with microinverter technology for maximum energy harvest.',
                'category' => 'INVERTER',
                'brand' => 'Enphase',
                'model' => 'IQ7+',
                'price' => 180.00,
                'power_rating' => 290,
                'stock_quantity' => 100,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 3,
            ],

            // Batteries
            [
                'name' => 'Lithium Battery Storage',
                'description' => 'High-capacity lithium-ion battery for home energy storage with 10-year warranty.',
                'category' => 'BATTERY',
                'brand' => 'Tesla',
                'model' => 'Powerwall 2',
                'price' => 7500.00,
                'capacity' => 13.5,
                'stock_quantity' => 10,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'LFP Battery System',
                'description' => 'Safe and long-lasting LiFePO4 battery system with excellent cycle life.',
                'category' => 'BATTERY',
                'brand' => 'BYD',
                'model' => 'Battery-Box Premium HVS 10.2',
                'price' => 6200.00,
                'capacity' => 10.2,
                'stock_quantity' => 12,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 2,
            ],

            // Mounting
            [
                'name' => 'Roof Mounting Kit',
                'description' => 'Complete aluminum mounting system for pitched roofs. Includes rails, clamps, and hardware.',
                'category' => 'MOUNTING',
                'brand' => 'IronRidge',
                'model' => 'XR100',
                'price' => 450.00,
                'stock_quantity' => 40,
                'unit' => 'set',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ground Mount System',
                'description' => 'Heavy-duty ground mounting structure for large-scale installations.',
                'category' => 'MOUNTING',
                'brand' => 'Unirac',
                'model' => 'RM10',
                'price' => 850.00,
                'stock_quantity' => 20,
                'unit' => 'set',
                'is_active' => true,
                'sort_order' => 2,
            ],

            // Accessories
            [
                'name' => 'Solar Cable Kit',
                'description' => 'UV-resistant solar cable with MC4 connectors. 50 meters per roll.',
                'category' => 'ACCESSORIES',
                'brand' => 'Amphenol',
                'model' => 'H1Z2Z2-K 4mm²',
                'price' => 120.00,
                'stock_quantity' => 60,
                'unit' => 'roll',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'DC Combiner Box',
                'description' => 'Weather-resistant DC combiner box with surge protection and fuses.',
                'category' => 'ACCESSORIES',
                'brand' => 'Schneider Electric',
                'model' => 'PVSCB-6',
                'price' => 280.00,
                'stock_quantity' => 35,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Monitoring System',
                'description' => 'Smart monitoring system with mobile app for real-time energy tracking.',
                'category' => 'ACCESSORIES',
                'brand' => 'SolarEdge',
                'model' => 'SE-MTR240-0-000',
                'price' => 350.00,
                'stock_quantity' => 25,
                'unit' => 'piece',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
