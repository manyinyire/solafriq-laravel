<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000),
            'status' => 'PENDING',
            'payment_status' => 'PENDING',
            'payment_method' => $this->faker->randomElement(['BANK_TRANSFER', 'CASH', 'CARD']),
            'tracking_number' => null,
            'installation_date' => null,
            'notes' => null,
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->address(),
            'is_gift' => false,
        ];
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'PAID',
        ]);
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PROCESSING',
            'payment_status' => 'PAID',
        ]);
    }

    /**
     * Indicate that the order is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'SHIPPED',
            'payment_status' => 'PAID',
            'tracking_number' => 'TRK-' . strtoupper(uniqid()),
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DELIVERED',
            'payment_status' => 'PAID',
            'tracking_number' => 'TRK-' . strtoupper(uniqid()),
        ]);
    }

    /**
     * Indicate that the order is declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'DECLINED',
        ]);
    }

    /**
     * Indicate that the order is a gift.
     */
    public function gift(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_gift' => true,
            'recipient_name' => $this->faker->name(),
            'recipient_email' => $this->faker->safeEmail(),
            'recipient_phone' => $this->faker->phoneNumber(),
            'recipient_address' => $this->faker->address(),
        ]);
    }
}
