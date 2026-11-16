<?php

use App\Livewire\Pages\CheckoutPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\QrisPayload;
use Illuminate\Support\Str;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');
});

function flowShop(string $name, array $overrides = []): Shop
{
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    return Shop::create(array_merge([
        'user_id' => $seller->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(5),
        'status' => 'approved',
    ], $overrides));
}

function flowProduct(Shop $shop, float $price, int $stock, string $name): Product
{
    return Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Kat '.Str::random(4), 'slug' => 'kat-'.Str::random(6)])->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(5),
        'price' => $price,
        'stock' => $stock,
        'status' => 'published',
    ]);
}

function flowBuyer(): User
{
    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    return $buyer;
}

/**
 * @param  list<array{product: Product, quantity: int, price?: float}>  $rows
 */
function flowCart(array $rows): void
{
    $cart = [];

    foreach ($rows as $row) {
        $cart[$row['product']->id] = [
            'product_id' => $row['product']->id,
            'name' => $row['product']->name,
            'price' => $row['price'] ?? (float) $row['product']->price,
            'image' => null,
            'quantity' => $row['quantity'],
            'shop_name' => $row['product']->shop->name,
            'shop_id' => $row['product']->shop_id,
        ];
    }

    session(['cart' => $cart, 'checkout_items' => array_keys($cart)]);
}

function flowCheckout(string $payment = 'cod', string $shipping = 'pickup'): Testable
{
    return Livewire::test(CheckoutPage::class)
        ->set('name', 'Pembeli Uji')
        ->set('phone', '081234567890')
        ->set('address', 'Jl. Uji No. 1')
        ->set('city', 'Cilacap')
        ->set('shippingMethod', $shipping)
        ->call('submitAddress')
        ->set('paymentMethod', $payment)
        ->call('submitPayment')
        ->call('submitOrder');
}

it('mengurangi stok dan menyimpan order milik toko', function () {
    $shop = flowShop('Toko Stok');
    $product = flowProduct($shop, 25000, 5, 'Produk Stok');

    $this->actingAs(flowBuyer());
    flowCart([['product' => $product, 'quantity' => 2]]);

    flowCheckout();

    $order = Order::sole();

    expect($order->shop_id)->toBe($shop->id)
        ->and((float) $order->subtotal)->toBe(50000.0)
        ->and((float) $order->total)->toBe(50000.0)
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->quantity)->toBe(2);

    expect($product->fresh()->stock)->toBe(3);
});

it('memecah checkout multi-toko menjadi satu order per toko', function () {
    $shopA = flowShop('Toko A');
    $productA = flowProduct($shopA, 10000, 5, 'Produk A');
    $shopB = flowShop('Toko B');
    $productB = flowProduct($shopB, 20000, 5, 'Produk B');

    $this->actingAs(flowBuyer());
    flowCart([
        ['product' => $productA, 'quantity' => 1],
        ['product' => $productB, 'quantity' => 3],
    ]);

    flowCheckout();

    expect(Order::count())->toBe(2);

    $orderA = Order::where('shop_id', $shopA->id)->sole();
    $orderB = Order::where('shop_id', $shopB->id)->sole();

    expect((float) $orderA->subtotal)->toBe(10000.0)
        ->and((float) $orderB->subtotal)->toBe(60000.0)
        ->and($orderA->items)->toHaveCount(1)
        ->and($orderB->items)->toHaveCount(1);

    expect($productA->fresh()->stock)->toBe(4)
        ->and($productB->fresh()->stock)->toBe(2);
});

it('menolak checkout ketika stok tidak mencukupi dan tidak mengubah apa pun', function () {
    $shop = flowShop('Toko Kurang');
    $product = flowProduct($shop, 25000, 3, 'Produk Terbatas');

    $this->actingAs(flowBuyer());
    flowCart([['product' => $product, 'quantity' => 10]]);

    flowCheckout()->assertDispatched('notify');

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(3);
});

it('menolak order ketika harga produk berubah sejak masuk keranjang', function () {
    $shop = flowShop('Toko Harga');
    $product = flowProduct($shop, 25000, 5, 'Produk Harga');

    $this->actingAs(flowBuyer());
    // Harga di keranjang sengaja tidak sama dengan DB.
    flowCart([['product' => $product, 'quantity' => 1, 'price' => 9999]]);

    flowCheckout()->assertDispatched('notify');

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(5);
});

it('mengarahkan ke halaman pembayaran untuk order qris', function () {
    $shop = flowShop('Toko QRIS', [
        'qris_static_payload' => QrisPayload::makeStaticDemo('Toko QRIS'),
        'qris_mode' => 'dynamic',
    ]);
    $product = flowProduct($shop, 25000, 5, 'Produk QRIS');

    $this->actingAs(flowBuyer());
    flowCart([['product' => $product, 'quantity' => 1]]);

    flowCheckout('qris')->assertRedirect(route('orders.pay', Order::sole()));

    $order = Order::sole();

    expect($order->payment_method)->toBe('qris')
        ->and($order->payment_status)->toBe('unpaid')
        ->and($order->qris_payload)->not->toBeNull();

    expect($product->fresh()->stock)->toBe(4);
});

it('menolak qris bila ada toko yang belum mengatur qris', function () {
    $shopA = flowShop('Toko Punya QRIS', [
        'qris_static_payload' => QrisPayload::makeStaticDemo('Toko Punya QRIS'),
        'qris_mode' => 'dynamic',
    ]);
    $shopB = flowShop('Toko Tanpa QRIS');
    $productA = flowProduct($shopA, 10000, 5, 'Produk A');
    $productB = flowProduct($shopB, 10000, 5, 'Produk B');

    $this->actingAs(flowBuyer());
    flowCart([
        ['product' => $productA, 'quantity' => 1],
        ['product' => $productB, 'quantity' => 1],
    ]);

    flowCheckout('qris')->assertDispatched('notify');

    expect(Order::count())->toBe(0);
});