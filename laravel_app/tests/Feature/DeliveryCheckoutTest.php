<?php

use App\Livewire\Pages\CheckoutPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function deliveryCheckoutFixtures(array $shopOverrides = []): array
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $shop = Shop::create(array_merge([
        'user_id' => $seller->id,
        'name' => 'Toko Ongkir',
        'slug' => 'toko-ongkir-'.uniqid(),
        'description' => 'Toko uji pengiriman',
        'status' => 'approved',
        'latitude' => -7.71810000,
        'longitude' => 109.01810000,
        'delivery_enabled' => true,
        'delivery_unit_meters' => 100,
        'delivery_rate' => 2000,
    ], $shopOverrides));

    $category = Category::create([
        'name' => 'Oleh-oleh',
        'slug' => 'oleh-oleh-'.uniqid(),
    ]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $category->id,
        'price' => 25000,
        'stock' => 10,
        'status' => 'published',
    ]);

    return compact('seller', 'buyer', 'shop', 'product');
}

function startDeliveryCheckout($buyer, $shop, $product): \Livewire\Features\SupportTesting\Testable
{
    $cart = [
        $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'image' => null,
            'quantity' => 2,
            'shop_name' => $shop->name,
            'shop_id' => $shop->id,
        ],
    ];

    session([
        'cart' => $cart,
        'checkout_items' => [$product->id],
    ]);

    return Livewire::actingAs($buyer)->test(CheckoutPage::class);
}

it('creates an order with distance-based shipping cost', function () {
    ['buyer' => $buyer, 'shop' => $shop, 'product' => $product] = deliveryCheckoutFixtures();

    startDeliveryCheckout($buyer, $shop, $product)
        ->assertSet('shippingMethod', 'delivery')
        ->set('name', 'Andi Pembeli')
        ->set('phone', '081234567890')
        ->set('address', 'Jl. Tes No. 1')
        ->set('city', 'Cilacap')
        ->call('setDeliveryLocation', -7.71710000, 109.01810000)
        ->call('submitAddress')
        ->assertSet('step', 'payment')
        ->set('paymentMethod', 'cod')
        ->call('submitPayment')
        ->call('submitOrder')
        ->assertSet('step', 'success');

    $order = Order::query()->first();

    expect($order)->not->toBeNull()
        ->and($order->shipping_method)->toBe('delivery')
        ->and((float) $order->delivery_latitude)->toBe(-7.71710000)
        ->and((float) $order->delivery_longitude)->toBe(109.01810000)
        ->and($order->delivery_distance_meters)->toBeGreaterThan(110)
        ->and($order->delivery_distance_meters)->toBeLessThan(113)
        ->and((int) $order->shipping_cost)->toBe(4000)
        ->and((int) $order->total)->toBe(54000);
});

it('keeps pickup orders free of shipping cost', function () {
    ['buyer' => $buyer, 'shop' => $shop, 'product' => $product] = deliveryCheckoutFixtures();

    startDeliveryCheckout($buyer, $shop, $product)
        ->set('shippingMethod', 'pickup')
        ->set('name', 'Andi Pembeli')
        ->set('phone', '081234567890')
        ->set('address', 'Jl. Tes No. 1')
        ->call('submitAddress')
        ->set('paymentMethod', 'cod')
        ->call('submitPayment')
        ->call('submitOrder');

    $order = Order::query()->first();

    expect($order->shipping_method)->toBe('pickup')
        ->and((int) $order->shipping_cost)->toBe(0)
        ->and($order->delivery_latitude)->toBeNull()
        ->and((int) $order->total)->toBe(50000);
});

it('blocks delivery checkout when the pin has not been set', function () {
    ['buyer' => $buyer, 'shop' => $shop, 'product' => $product] = deliveryCheckoutFixtures();

    startDeliveryCheckout($buyer, $shop, $product)
        ->set('name', 'Andi Pembeli')
        ->set('phone', '081234567890')
        ->set('address', 'Jl. Tes No. 1')
        ->call('submitAddress')
        ->assertHasErrors(['deliveryLatitude'])
        ->assertSet('step', 'address');

    expect(Order::query()->count())->toBe(0);
});

it('does not offer delivery when the shop has no rate', function () {
    ['buyer' => $buyer, 'shop' => $shop, 'product' => $product] = deliveryCheckoutFixtures([
        'delivery_enabled' => false,
        'delivery_rate' => 0,
    ]);

    $component = startDeliveryCheckout($buyer, $shop, $product)
        ->assertSet('shippingMethod', 'pickup');

    expect(collect($component->get('shippingOptions'))->pluck('id')->all())->toBe(['pickup']);
});
