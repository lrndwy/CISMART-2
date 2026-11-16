<?php

use App\Livewire\Pages\ProductDetailPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');
});

/**
 * @return array{0: Product, 1: User, 2: Shop}
 */
function reviewFixture(string $orderStatus): array
{
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Ulasan',
        'slug' => 'toko-ulasan-'.Str::random(5),
        'status' => 'approved',
    ]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Kat '.Str::random(4), 'slug' => 'kat-'.Str::random(6)])->id,
        'name' => 'Produk Ulasan',
        'slug' => 'produk-ulasan-'.Str::random(5),
        'price' => 25000,
        'stock' => 5,
        'status' => 'published',
    ]);

    $order = Order::create([
        'user_id' => $buyer->id,
        'shop_id' => $shop->id,
        'shipping_name' => 'Pembeli Uji',
        'shipping_phone' => '081234567890',
        'shipping_address' => 'Jl. Uji No. 1',
        'shipping_city' => 'Cilacap',
        'payment_method' => 'cod',
        'payment_status' => 'not_required',
        'shipping_method' => 'pickup',
        'subtotal' => 25000,
        'shipping_cost' => 0,
        'total' => 25000,
        'status' => $orderStatus,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'shop_id' => $shop->id,
        'product_name' => $product->name,
        'shop_name' => $shop->name,
        'price' => 25000,
        'quantity' => 1,
        'subtotal' => 25000,
    ]);

    return [$product->refresh(), $buyer, $shop];
}

it('menolak ulasan produk bila pesanan belum diterima', function () {
    [$product, $buyer] = reviewFixture(Order::STATUS_SHIPPED);

    $this->actingAs($buyer);

    Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 5)
        ->set('comment', 'Produk bagus sekali, pengiriman cepat.')
        ->call('submitReview')
        ->assertDispatched('notify');

    expect(Review::count())->toBe(0);
});

it('menerima ulasan produk setelah pesanan delivered dan memperbarui agregat', function () {
    [$product, $buyer] = reviewFixture(Order::STATUS_DELIVERED);

    $this->actingAs($buyer);

    Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
        ->set('rating', 4)
        ->set('comment', 'Kualitas produk sesuai deskripsi penjual.')
        ->call('submitReview')
        ->assertHasNoErrors();

    $review = Review::sole();

    expect($review->product_id)->toBe($product->id)
        ->and($review->shop_id)->toBe($product->shop_id)
        ->and($review->order_item_id)->not->toBeNull();

    $product->refresh();

    expect((float) $product->rating)->toBe(4.0)
        ->and($product->total_reviews)->toBe(1);

    // Rating toko ikut terhitung dari ulasan produk.
    expect((float) $product->shop->fresh()->rating)->toBe(4.0);
});

it('menolak ulasan kedua untuk produk yang sama', function () {
    [$product, $buyer] = reviewFixture(Order::STATUS_DELIVERED);

    $this->actingAs($buyer);

    foreach ([1, 2] as $attempt) {
        Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
            ->set('rating', 5)
            ->set('comment', 'Ulasan percobaan nomor '.$attempt.' untuk produk ini.')
            ->call('submitReview');
    }

    expect(Review::count())->toBe(1);
});

it('menolak ulasan toko bila pembeli belum menerima pesanan', function () {
    [$product, $buyer, $shop] = reviewFixture(Order::STATUS_PENDING);

    expect($buyer->hasOrderedFrom($shop))->toBeFalse();
});

it('mengizinkan ulasan toko setelah pesanan delivered', function () {
    [$product, $buyer, $shop] = reviewFixture(Order::STATUS_DELIVERED);

    expect($buyer->hasOrderedFrom($shop))->toBeTrue()
        ->and($buyer->hasDeliveredProduct($product))->toBeTrue();
});

it('mengisi kolom sold_count saat order delivered (sort terpopuler bekerja)', function () {
    [$product] = reviewFixture(Order::STATUS_SHIPPED);

    $order = Order::sole();
    $order->transitionTo(Order::STATUS_DELIVERED);

    expect($product->fresh()->sold_count)->toBe(1);
});