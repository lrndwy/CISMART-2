<?php

use App\Livewire\Pages\CheckoutPage;
use App\Livewire\Pages\OrdersPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function buyerWithCart(): array
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Kedai Uji',
        'slug' => 'kedai-uji-'.uniqid(),
        'description' => 'Toko uji',
        'status' => 'approved',
    ]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Kopi', 'slug' => 'kopi-'.uniqid()])->id,
        'name' => 'Kopi Robusta Cilacap',
        'price' => 20000,
        'stock' => 10,
        'status' => 'published',
    ]);

    return compact('seller', 'buyer', 'shop', 'product');
}

it('shows date, items, and shop on the buyer order history', function () {
    ['buyer' => $buyer, 'shop' => $shop, 'product' => $product] = buyerWithCart();

    $order = Order::create([
        'user_id' => $buyer->id,
        'shipping_name' => $buyer->name,
        'shipping_phone' => '081234567890',
        'shipping_address' => 'Jl. Uji No. 1',
        'shipping_city' => 'Cilacap',
        'payment_method' => 'cod',
        'payment_status' => 'not_required',
        'shipping_method' => 'pickup',
        'subtotal' => 40000,
        'shipping_cost' => 0,
        'total' => 40000,
        'status' => 'pending',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'shop_id' => $shop->id,
        'product_name' => $product->name,
        'shop_name' => $shop->name,
        'price' => 20000,
        'quantity' => 2,
        'subtotal' => 40000,
    ]);

    $this->actingAs($buyer);

    Livewire::test(OrdersPage::class)
        ->assertSee($order->order_number)
        ->assertSee($order->created_at->translatedFormat('d F Y'))
        ->assertSee('Kopi Robusta Cilacap')
        ->assertSee('Kedai Uji')
        ->assertSee('Bayar di Tempat')
        ->assertSee('40.000');
});

it('offers only QRIS and pay-in-place as payment methods', function () {
    ['buyer' => $buyer, 'shop' => $shop, 'product' => $product] = buyerWithCart();

    $this->actingAs($buyer);
    session([
        'cart' => [
            $product->id => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => null,
                'quantity' => 1,
                'shop_name' => $shop->name,
                'shop_id' => $shop->id,
            ],
        ],
        'checkout_items' => [$product->id],
    ]);

    $component = Livewire::test(CheckoutPage::class);

    expect(collect($component->get('paymentOptions'))->pluck('id')->all())->toBe(['qris', 'cod']);

    $component->set('paymentMethod', 'cod')
        ->call('submitPayment')
        ->assertSet('step', 'review')
        ->assertHasNoErrors();
});
