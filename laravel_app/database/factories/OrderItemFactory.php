<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory()->create();
        $quantity = fake()->numberBetween(1, 5);
        $price = $product->price;
        $subtotal = $price * $quantity;

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'shop_id' => $product->shop_id,
            'product_name' => $product->name,
            'shop_name' => $product->shop->name ?? 'Unknown Shop',
            'product_image' => $product->image,
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
        ];
    }
}
