<?php

use App\Livewire\Pages\ProductsPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('seller');
});

function sortingShop(): Shop
{
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    return Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Sortir',
        'slug' => 'toko-sortir-'.Str::random(5),
        'status' => 'approved',
    ]);
}

function sortingProduct(Shop $shop, string $name, float $rating, int $sold): Product
{
    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Kat '.Str::random(4), 'slug' => 'kat-'.Str::random(6)])->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(5),
        'price' => 25000,
        'stock' => 5,
        'status' => 'published',
    ]);

    // Kolom agregat tidak mass-assignable — diisi langsung seperti hasil hitungan.
    $product->newQuery()->whereKey($product->getKey())->update([
        'rating' => $rating,
        'total_reviews' => $sold,
        'sold_count' => $sold,
    ]);

    return $product->refresh();
}

/**
 * Regresi F-02: sebelumnya sort memakai kolom products.rating/sold yang tidak
 * ada, sehingga halaman meledak begitu katalog tidak kosong.
 */
it('mengurutkan berdasarkan rating tanpa error', function () {
    $shop = sortingShop();
    sortingProduct($shop, 'Produk Rating Rendah', 2.0, 1);
    sortingProduct($shop, 'Produk Rating Tinggi', 4.8, 1);

    $html = Livewire::test(ProductsPage::class)
        ->set('sortBy', 'rating')
        ->assertOk()
        ->html();

    expect(strpos($html, 'Produk Rating Tinggi'))->toBeLessThan(strpos($html, 'Produk Rating Rendah'));
});

it('mengurutkan berdasarkan jumlah terjual tanpa error', function () {
    $shop = sortingShop();
    sortingProduct($shop, 'Produk Kurang Laku', 3.0, 1);
    sortingProduct($shop, 'Produk Paling Laku', 3.0, 99);

    $html = Livewire::test(ProductsPage::class)
        ->set('sortBy', 'popular')
        ->assertOk()
        ->html();

    expect(strpos($html, 'Produk Paling Laku'))->toBeLessThan(strpos($html, 'Produk Kurang Laku'));
});

it('menyaring berdasarkan rating minimum', function () {
    $shop = sortingShop();
    sortingProduct($shop, 'Produk Dibawah Filter', 2.0, 1);
    sortingProduct($shop, 'Produk Di Atas Filter', 4.5, 1);

    Livewire::test(ProductsPage::class)
        ->set('minRating', 4)
        ->assertOk()
        ->assertSee('Produk Di Atas Filter')
        ->assertDontSee('Produk Dibawah Filter');
});