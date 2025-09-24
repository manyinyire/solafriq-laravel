<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SolarSystem>
 */
class SolarSystemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $capacity = fake()->randomElement(['1kW', '2kW', '3kW', '5kW', '10kW']);
        $basePrice = fake()->numberBetween(500000, 2000000);
        $originalPrice = $basePrice * 1.2; // 20% more than base price

        return [
            'name' => 'SolaFriq ' . $capacity . ' Solar System',
            'description' => fake()->paragraphs(3, true),
            'short_description' => fake()->sentence(20),
            'capacity' => $capacity,
            'price' => $basePrice,
            'original_price' => $originalPrice,
            'installment_price' => $basePrice * 1.15, // 15% more for installments
            'installment_months' => fake()->randomElement([6, 12, 18, 24]),
            'image_url' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=600&fit=crop',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1497440001374-f26997328c1b?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800&h=600&fit=crop',
            ],
            'use_case' => fake()->randomElement([
                'Perfect for homes with low energy consumption',
                'Ideal for medium-sized homes and small offices',
                'Great for large homes and commercial use',
                'Suitable for heavy energy consumers',
            ]),
            'gradient_colors' => fake()->randomElement([
                'from-orange-400 to-yellow-500',
                'from-blue-500 to-teal-500',
                'from-green-400 to-blue-500',
                'from-purple-400 to-pink-500',
            ]),
            'is_popular' => fake()->boolean(30), // 30% chance of being popular
            'is_featured' => fake()->boolean(20), // 20% chance of being featured
            'is_active' => fake()->boolean(90), // 90% chance of being active
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Make the system popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_popular' => true,
        ]);
    }

    /**
     * Make the system featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Make the system inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}