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
        $subtotal = fake()->randomFloat(2, 50000, 500000);
        $shippingCost = fake()->randomElement([0, 15000, 25000, 35000]);
        $total = $subtotal + $shippingCost;

        return [
            'order_number' => 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4)),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'shipping_name' => fake()->name(),
            'shipping_phone' => fake()->numerify('08##########'),
            'shipping_address' => fake()->address(),
            'shipping_city' => fake()->randomElement(['Cilacap', 'Purwokerto', 'Yogyakarta', 'Semarang', 'Surabaya']),
            'shipping_postal_code' => fake()->numerify('53###'),
            'shipping_notes' => fake()->optional(0.3)->sentence(),
            'payment_method' => fake()->randomElement(['whatsapp-order', 'bank-transfer', 'cod']),
            'shipping_method' => fake()->randomElement(['regular', 'express', 'same-day', 'none']),
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'status' => fake()->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']),
            'shop_whatsapp' => '6288225292279',
            'whatsapp_sent_at' => fake()->optional(0.8)->dateTimeBetween('-30 days', 'now'),
            'notes' => fake()->optional(0.2)->sentence(),
            'confirmed_at' => fake()->optional(0.6)->dateTimeBetween('-25 days', 'now'),
            'cancelled_at' => fake()->optional(0.1)->dateTimeBetween('-20 days', 'now'),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'confirmed_at' => null,
            'cancelled_at' => null,
        ]);
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'delivered',
            'confirmed_at' => fake()->dateTimeBetween('-20 days', '-10 days'),
        ]);
    }
}
