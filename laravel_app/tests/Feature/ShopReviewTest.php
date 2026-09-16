<?php

use App\Livewire\Pages\ShopDetailPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function shopReviewFixtures(?User $reviewer = null): array
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $buyer = $reviewer ?? User::factory()->create();
    if (! $reviewer) {
        $buyer->assignRole('buyer');
    }

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Ulasan',
        'slug' => 'toko-ulasan-'.uniqid(),
        'description' => 'Toko uji ulasan',
        'status' => 'approved',
    ]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Oleh-oleh', 'slug' => 'oleh-oleh-'.uniqid()])->id,
        'status' => 'published',
        'stock' => 5,
    ]);

    $order = Order::create([
        'user_id' => $buyer->id,
        'shipping_name' => $buyer->name,
        'shipping_phone' => '081234567890',
        'shipping_address' => 'Jl. Uji No. 1',
        'shipping_city' => 'Cilacap',
        'payment_method' => 'cod',
        'payment_status' => 'not_required',
        'shipping_method' => 'pickup',
        'subtotal' => 20000,
        'shipping_cost' => 0,
        'total' => 20000,
        'status' => 'delivered',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'shop_id' => $shop->id,
        'product_name' => $product->name,
        'shop_name' => $shop->name,
        'price' => 20000,
        'quantity' => 1,
        'subtotal' => 20000,
    ]);

    return compact('seller', 'buyer', 'shop', 'product', 'order');
}

it('lets a buyer who ordered from the shop submit a review and update the rating', function () {
    ['buyer' => $buyer, 'shop' => $shop] = shopReviewFixtures();

    Livewire::actingAs($buyer)->test(ShopDetailPage::class, ['slug' => $shop->slug])
        ->call('setActiveTab', 'reviews')
        ->set('rating', 5)
        ->set('comment', 'Produk bagus sekali, pengiriman cepat.')
        ->call('submitReview')
        ->assertHasNoErrors();

    expect(Review::count())->toBe(1);

    $shop->refresh();
    expect((float) $shop->rating)->toBe(5.0)
        ->and($shop->total_reviews)->toBe(1);
});

it('validates rating and comment', function () {
    ['buyer' => $buyer, 'shop' => $shop] = shopReviewFixtures();

    Livewire::actingAs($buyer)->test(ShopDetailPage::class, ['slug' => $shop->slug])
        ->call('setActiveTab', 'reviews')
        ->set('rating', 0)
        ->set('comment', 'pendek')
        ->call('submitReview')
        ->assertHasErrors(['rating', 'comment']);

    expect(Review::count())->toBe(0);
});

it('allows only one review per buyer per shop', function () {
    ['buyer' => $buyer, 'shop' => $shop] = shopReviewFixtures();

    Livewire::actingAs($buyer)->test(ShopDetailPage::class, ['slug' => $shop->slug])
        ->call('setActiveTab', 'reviews')
        ->set('rating', 4)
        ->set('comment', 'Pelayanan ramah dan cepat.')
        ->call('submitReview');

    Livewire::actingAs($buyer)->test(ShopDetailPage::class, ['slug' => $shop->slug])
        ->call('setActiveTab', 'reviews')
        ->set('rating', 2)
        ->set('comment', 'Percobaan ulasan kedua dari pembeli.')
        ->call('submitReview')
        ->assertDontSee('Kirim Ulasan');

    expect(Review::count())->toBe(1);
});

it('refuses a review from a user who never ordered from the shop', function () {
    ['shop' => $shop] = shopReviewFixtures();

    $stranger = User::factory()->create();
    $stranger->assignRole('buyer');

    Livewire::actingAs($stranger)->test(ShopDetailPage::class, ['slug' => $shop->slug])
        ->call('setActiveTab', 'reviews')
        ->assertDontSee('Kirim Ulasan')
        ->set('rating', 5)
        ->set('comment', 'Produk bagus sekali, pengiriman cepat.')
        ->call('submitReview');

    expect(Review::count())->toBe(0);
});

it('recalculates the shop rating when a review is deleted', function () {
    ['buyer' => $buyer, 'shop' => $shop] = shopReviewFixtures();

    $other = User::factory()->create();
    $other->assignRole('buyer');

    $first = Review::create([
        'shop_id' => $shop->id,
        'user_id' => $buyer->id,
        'rating' => 5,
        'comment' => 'Ulasan pertama yang sangat memuaskan.',
    ]);
    Review::create([
        'shop_id' => $shop->id,
        'user_id' => $other->id,
        'rating' => 3,
        'comment' => 'Ulasan kedua yang biasa saja.',
    ]);

    $shop->refresh();
    expect((float) $shop->rating)->toBe(4.0)
        ->and($shop->total_reviews)->toBe(2);

    $first->delete();

    $shop->refresh();
    expect((float) $shop->rating)->toBe(3.0)
        ->and($shop->total_reviews)->toBe(1);
});

it('serves real reviews and no contact buttons on the shop page', function () {
    ['shop' => $shop] = shopReviewFixtures();

    Livewire::test(ShopDetailPage::class, ['slug' => $shop->slug])
        ->call('setActiveTab', 'reviews')
        ->assertDontSee('Chat Toko')
        ->assertDontSee('Sari Wijaya')
        ->assertDontSee('Kirim Ulasan')
        ->assertSee('Belum ada ulasan');

    $reviewer = User::factory()->create();
    Review::create([
        'shop_id' => $shop->id,
        'user_id' => $reviewer->id,
        'rating' => 5,
        'comment' => 'Kualitas produknya luar biasa bagus.',
    ]);

    Livewire::test(ShopDetailPage::class, ['slug' => $shop->slug])
        ->call('setActiveTab', 'reviews')
        ->assertSee('Kualitas produknya luar biasa bagus.');
});
