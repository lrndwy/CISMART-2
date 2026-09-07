<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users and products
        $users = User::all();
        $products = Product::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please seed users and products first.');
            return;
        }

        // Create 20 orders with items
        $this->command->info('Creating orders...');

        foreach ($users->random(min(10, $users->count())) as $user) {
            // Create 1-3 orders per user
            $orderCount = rand(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                // Determine order status distribution
                $status = match (rand(1, 10)) {
                    1, 2, 3 => 'pending',      // 30%
                    4, 5 => 'confirmed',        // 20%
                    6, 7 => 'processing',       // 20%
                    8 => 'shipped',             // 10%
                    9 => 'delivered',           // 10%
                    10 => 'cancelled',          // 10%
                };

                // Create order
                $order = Order::factory()
                    ->state([
                        'user_id' => $user->id,
                        'status' => $status,
                    ])
                    ->create();

                // Add 1-5 items to the order
                $itemCount = rand(1, 5);
                $selectedProducts = $products->random(min($itemCount, $products->count()));

                $subtotal = 0;

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $price = $product->price;
                    $itemSubtotal = $price * $quantity;
                    $subtotal += $itemSubtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'shop_id' => $product->shop_id,
                        'product_name' => $product->name,
                        'shop_name' => $product->shop->name ?? 'Unknown Shop',
                        'product_image' => $product->image,
                        'price' => $price,
                        'quantity' => $quantity,
                        'subtotal' => $itemSubtotal,
                    ]);
                }

                // Update order totals
                $shippingCost = $order->shipping_method === 'none' ? 0 : $order->shipping_cost;
                $order->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $shippingCost,
                ]);
            }
        }

        $orderCount = Order::count();
        $itemCount = OrderItem::count();

        $this->command->info("Created {$orderCount} orders with {$itemCount} items.");
    }
}
