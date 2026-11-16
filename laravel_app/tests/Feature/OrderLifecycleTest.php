<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');
});

/**
 * @return array{0: Order, 1: Product, 2: User, 3: Shop}
 */
function lifecycleFixture(int $quantity = 2, int $stock = 5, array $orderAttrs = []): array
{
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Lifecycle',
        'slug' => 'toko-lifecycle-'.Str::random(5),
        'status' => 'approved',
    ]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Kat '.Str::random(4), 'slug' => 'kat-'.Str::random(6)])->id,
        'name' => 'Produk Lifecycle',
        'slug' => 'produk-lifecycle-'.Str::random(5),
        'price' => 25000,
        'stock' => $stock,
        'status' => 'published',
    ]);

    // Stok sudah "dipesan" saat checkout.
    $product->decrement('stock', $quantity);

    $order = Order::create(array_merge([
        'user_id' => $buyer->id,
        'shop_id' => $shop->id,
        'shipping_name' => 'Pembeli Uji',
        'shipping_phone' => '081234567890',
        'shipping_address' => 'Jl. Uji No. 1',
        'shipping_city' => 'Cilacap',
        'payment_method' => 'qris',
        'payment_status' => 'unpaid',
        'shipping_method' => 'pickup',
        'subtotal' => 25000 * $quantity,
        'shipping_cost' => 0,
        'total' => 25000 * $quantity,
        'status' => Order::STATUS_PENDING,
    ], $orderAttrs));

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'shop_id' => $shop->id,
        'product_name' => $product->name,
        'shop_name' => $shop->name,
        'product_image' => null,
        'price' => 25000,
        'quantity' => $quantity,
        'subtotal' => 25000 * $quantity,
    ]);

    return [$order->refresh(), $product->refresh(), $buyer, $shop];
}

it('menolak konfirmasi order qris yang belum dibayar', function () {
    [$order] = lifecycleFixture();

    expect($order->canTransitionTo(Order::STATUS_CONFIRMED))->toBeFalse()
        ->and($order->transitionTo(Order::STATUS_CONFIRMED))->toBeFalse()
        ->and($order->fresh()->status)->toBe(Order::STATUS_PENDING);
});

it('mengonfirmasi order setelah pembayaran terverifikasi dan mencatat riwayat', function () {
    [$order] = lifecycleFixture();

    $order->update(['payment_status' => 'verified']);

    expect($order->transitionTo(Order::STATUS_CONFIRMED))->toBeTrue();

    $order->refresh();

    expect($order->status)->toBe(Order::STATUS_CONFIRMED)
        ->and($order->confirmed_at)->not->toBeNull()
        ->and($order->statusHistories()->count())->toBe(1);

    $history = $order->statusHistories()->sole();

    expect($history->from_status)->toBe(Order::STATUS_PENDING)
        ->and($history->to_status)->toBe(Order::STATUS_CONFIRMED);
});

it('mengizinkan konfirmasi order COD tanpa verifikasi pembayaran', function () {
    [$order] = lifecycleFixture(orderAttrs: [
        'payment_method' => 'cod',
        'payment_status' => 'not_required',
    ]);

    expect($order->transitionTo(Order::STATUS_CONFIRMED))->toBeTrue()
        ->and($order->fresh()->status)->toBe(Order::STATUS_CONFIRMED);
});

it('menambah sold_count saat order selesai', function () {
    [$order, $product] = lifecycleFixture(orderAttrs: [
        'payment_method' => 'cod',
        'payment_status' => 'not_required',
    ]);

    foreach ([Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED] as $status) {
        expect($order->transitionTo($status))->toBeTrue();
        $order->refresh();
    }

    expect($product->fresh()->sold_count)->toBe(2);
});

it('mengembalikan stok dan sold_count saat order dibatalkan', function () {
    [$order, $product] = lifecycleFixture();

    expect($product->fresh()->stock)->toBe(3);

    expect($order->transitionTo(Order::STATUS_CANCELLED, null, 'uji batal'))->toBeTrue();

    expect($product->fresh()->stock)->toBe(5)
        ->and($order->fresh()->cancelled_at)->not->toBeNull()
        ->and($order->statusHistories()->count())->toBe(1);
});

it('menolak transisi yang tidak sah (pending langsung delivered)', function () {
    [$order] = lifecycleFixture();

    expect($order->canTransitionTo(Order::STATUS_DELIVERED))->toBeFalse();
});

it('membatalkan pesanan kedaluwarsa lewat perintah orders:expire', function () {
    [$order, $product] = lifecycleFixture();

    $order->update(['expires_at' => now()->subHour()]);

    $this->artisan('orders:expire')->assertSuccessful();

    expect($order->fresh()->status)->toBe(Order::STATUS_CANCELLED)
        ->and($product->fresh()->stock)->toBe(5);
});

it('tidak membatalkan pesanan kedaluwarsa yang sudah terbayar', function () {
    [$order] = lifecycleFixture(orderAttrs: ['payment_status' => 'verified']);

    $order->update(['expires_at' => now()->subHour()]);

    $this->artisan('orders:expire')->assertSuccessful();

    expect($order->fresh()->status)->toBe(Order::STATUS_PENDING);
});

it('mengizinkan pembeli membatalkan order belum terbayar, menolak setelah terverifikasi', function () {
    [$order, , $buyer] = lifecycleFixture();

    expect($buyer->can('cancel', $order))->toBeTrue();

    $order->update(['payment_status' => 'verified']);

    expect($buyer->can('cancel', $order->fresh()))->toBeFalse();
});

it('membatasi penjual hanya pada order tokonya sendiri', function () {
    [$order] = lifecycleFixture();

    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('seller');
    Shop::create([
        'user_id' => $otherSeller->id,
        'name' => 'Toko Lain',
        'slug' => 'toko-lain-'.Str::random(5),
        'status' => 'approved',
    ]);

    expect($otherSeller->can('view', $order))->toBeFalse();
});