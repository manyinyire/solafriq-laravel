<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'SOLAR_PANEL',
            'INVERTER',
            'BATTERY',
            'CHARGE_CONTROLLER',
            'MOUNTING',
            'CABLES',
            'ACCESSORIES'
        ];

        $category = $this->faker->randomElement($categories);

        return [
            'name' => $this->getNameForCategory($category),
            'description' => $this->faker->paragraph(),
            'category' => $category,
            'brand' => $this->faker->randomElement(['SunPower', 'LG', 'Tesla', 'Canadian Solar', 'Jinko Solar', 'Victron Energy']),
            'model' => strtoupper($this->faker->bothify('??-###')),
            'price' => $this->faker->randomFloat(2, 50, 5000),
            'image_url' => '/images/products/' . strtolower($category) . '.jpg',
            'specifications' => json_encode([
                'warranty' => $this->faker->randomElement(['5 years', '10 years', '25 years']),
                'weight' => $this->faker->numberBetween(5, 50) . ' kg',
                'dimensions' => $this->faker->numberBetween(100, 200) . 'x' . $this->faker->numberBetween(100, 200) . 'x' . $this->faker->numberBetween(3, 10) . ' cm',
            ]),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'unit' => 'piece',
            'power_rating' => in_array($category, ['SOLAR_PANEL', 'INVERTER']) ? $this->faker->randomFloat(2, 100, 10000) : null,
            'capacity' => $category === 'BATTERY' ? $this->faker->randomFloat(2, 50, 500) : null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Get a realistic name for the category
     */
    private function getNameForCategory(string $category): string
    {
        $names = [
            'SOLAR_PANEL' => [
                'Monocrystalline Solar Panel 400W',
                'Polycrystalline Solar Panel 300W',
                'Bifacial Solar Panel 500W',
                'Half-Cut Cell Solar Panel 450W',
            ],
            'INVERTER' => [
                'Grid-Tied Inverter 5kW',
                'Hybrid Inverter 10kW',
                'Off-Grid Inverter 3kW',
                'Microinverter 1.2kW',
            ],
            'BATTERY' => [
                'Lithium Battery 200Ah',
                'Lead Acid Battery 150Ah',
                'LiFePO4 Battery 100Ah',
                'Deep Cycle Battery 250Ah',
            ],
            'CHARGE_CONTROLLER' => [
                'MPPT Charge Controller 60A',
                'PWM Charge Controller 30A',
                'Solar Charge Controller 40A',
            ],
            'MOUNTING' => [
                'Roof Mounting Kit',
                'Ground Mount System',
                'Pole Mount Bracket',
                'Adjustable Tilt Mount',
            ],
            'CABLES' => [
                'Solar DC Cable 6mm',
                'MC4 Connector Cable',
                'Battery Connection Cable',
                'Extension Cable 10m',
            ],
            'ACCESSORIES' => [
                'Junction Box',
                'Cable Clips Set',
                'Fuse Holder',
                'Solar Panel Cleaning Kit',
            ],
        ];

        return $this->faker->randomElement($names[$category] ?? ['Product']);
    }

    /**
     * Indicate that the product is a solar panel.
     */
    public function solarPanel(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'SOLAR_PANEL',
            'name' => 'Monocrystalline Solar Panel',
            'power_rating' => $this->faker->randomElement([300, 350, 400, 450, 500]),
            'capacity' => null,
        ]);
    }

    /**
     * Indicate that the product is an inverter.
     */
    public function inverter(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'INVERTER',
            'name' => 'Hybrid Solar Inverter',
            'power_rating' => $this->faker->randomElement([3000, 5000, 8000, 10000]),
            'capacity' => null,
        ]);
    }

    /**
     * Indicate that the product is a battery.
     */
    public function battery(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'BATTERY',
            'name' => 'Lithium Battery',
            'power_rating' => null,
            'capacity' => $this->faker->randomElement([100, 150, 200, 250, 300]),
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
