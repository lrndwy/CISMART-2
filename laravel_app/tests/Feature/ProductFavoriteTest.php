<?php

use App\Livewire\Pages\ProductDetailPage;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function favoriteFixtures(): array
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Favorit',
        'slug' => 'toko-favorit-'.uniqid(),
        'description' => 'Toko uji favorit',
        'status' => 'approved',
    ]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Oleh-oleh', 'slug' => 'oleh-oleh-'.uniqid()])->id,
        'status' => 'published',
        'stock' => 5,
    ]);

    return compact('seller', 'shop', 'product');
}

it('redirects a guest to login when favoriting', function () {
    ['product' => $product] = favoriteFixtures();

    Livewire::test(ProductDetailPage::class, ['slug' => $product->slug])
        ->call('toggleFavorite')
        ->assertRedirect();

    expect(Favorite::count())->toBe(0);
});

it('lets a user favorite a product once and unfavorite it', function () {
    ['product' => $product] = favoriteFixtures();

    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    Livewire::actingAs($buyer)->test(ProductDetailPage::class, ['slug' => $product->slug])
        ->call('toggleFavorite')
        ->assertSet('isFavorited', true)
        ->assertSee('Difavoritkan');

    expect(Favorite::where('user_id', $buyer->id)->where('product_id', $product->id)->count())->toBe(1);

    Livewire::actingAs($buyer)->test(ProductDetailPage::class, ['slug' => $product->slug])
        ->assertSet('isFavorited', true)
        ->call('toggleFavorite')
        ->assertSet('isFavorited', false);

    expect(Favorite::count())->toBe(0);
});

it('keeps exactly one favorite row per user and product', function () {
    ['product' => $product] = favoriteFixtures();

    $buyerA = User::factory()->create();
    $buyerA->assignRole('buyer');
    $buyerB = User::factory()->create();
    $buyerB->assignRole('buyer');

    Livewire::actingAs($buyerA)->test(ProductDetailPage::class, ['slug' => $product->slug])
        ->call('toggleFavorite')
        ->assertSee('1');

    // Second user adds their own single favorite; the first user's is untouched.
    Livewire::actingAs($buyerB)->test(ProductDetailPage::class, ['slug' => $product->slug])
        ->call('toggleFavorite');

    expect(Favorite::where('user_id', $buyerA->id)->count())->toBe(1)
        ->and(Favorite::where('user_id', $buyerB->id)->count())->toBe(1)
        ->and(Favorite::where('product_id', $product->id)->count())->toBe(2);
});
