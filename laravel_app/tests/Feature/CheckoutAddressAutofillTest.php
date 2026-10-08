<?php

use App\Livewire\Pages\CheckoutPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/**
 * Pembeli dengan satu produk dari toko yang mengaktifkan pengiriman.
 */
function deliveryBuyerWithCart(): User
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Ongkir',
        'slug' => 'toko-ongkir-'.uniqid(),
        'description' => 'Toko uji pengiriman',
        'status' => 'approved',
        'latitude' => -7.71810000,
        'longitude' => 109.01810000,
        'delivery_enabled' =>true,
        'delivery_unit_meters' => 100,
        'delivery_rate' => 2000,
    ]);

    $category = Category::create(['name' => 'Oleh-oleh', 'slug' => 'oleh-oleh-'.uniqid()]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $category->id,
        'price' => 25000,
        'stock' => 10,
        'status' => 'published',
    ]);

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

    return $buyer;
}

it('fills the shipping address from the map pin', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'road' => 'Jalan Gatot Subroto',
                'house_number' => '12',
                'village' => 'Cilacap Tengah',
                'city' => 'Cilacap',
                'postcode' => '53212',
            ],
        ]),
    ]);

    $buyer = deliveryBuyerWithCart();

    Livewire::actingAs($buyer)
        ->test(CheckoutPage::class)
        ->call('setDeliveryLocation', -7.71710000, 109.01810000)
        ->assertSet('address', 'Jalan Gatot Subroto 12, Cilacap Tengah, 53212');
});

it('uses the county when nominatim puts a kecamatan value in the city field', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'neighbourhood' => 'RW 10 Kelurahan Sidanegara',
                'village' => 'Sidanegara',
                'city' => 'Kelurahan Sidanegara Kecamatan Cilacap',
                'county' => 'Cilacap',
                'postcode' => '53212',
            ],
        ]),
    ]);

    $buyer = deliveryBuyerWithCart();

    Livewire::actingAs($buyer)
        ->test(CheckoutPage::class)
        ->call('setDeliveryLocation', -7.71810000, 109.01810000)
        ->assertSet('address', 'RW 10 Kelurahan Sidanegara, Cilacap, 53212');
});

it('uses the county when nominatim puts a kelurahan value in the city field', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'road' => '7',
                'neighbourhood' => 'RW 01 Kelurahan Tegalkamulyan',
                'city' => 'Kelurahan Tegalkamulyan, Cilacap',
                'county' => 'Cilacap',
                'postcode' => '53212',
            ],
        ]),
    ]);

    $buyer = deliveryBuyerWithCart();

    Livewire::actingAs($buyer)
        ->test(CheckoutPage::class)
        ->call('setDeliveryLocation', -7.72650000, 109.02550000)
        ->assertSet('address', '7, RW 01 Kelurahan Tegalkamulyan, Cilacap, 53212');
});

it('stops overwriting the address once the buyer edits it manually', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => ['road' => 'Jalan Dari Peta', 'city' => 'Cilacap'],
        ]),
    ]);

    $buyer = deliveryBuyerWithCart();

    $component = Livewire::actingAs($buyer)->test(CheckoutPage::class);

    $component->call('setDeliveryLocation', -7.71710000, 109.01810000)
        ->assertSet('address', 'Jalan Dari Peta, Cilacap');

    // Pembeli menyunting sendiri alamatnya...
    $component->set('address', 'Rumah saya, RT 03 RW 05');

    // ...lalu menggeser pin. Alamat manual tidak boleh tertimpa.
    $component->call('setDeliveryLocation', -7.71610000, 109.01910000)
        ->assertSet('address', 'Rumah saya, RT 03 RW 05');
});

it('keeps checkout usable when reverse geocoding fails', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response('upstream down', 500),
    ]);

    $buyer = deliveryBuyerWithCart();

    Livewire::actingAs($buyer)
        ->test(CheckoutPage::class)
        ->call('setDeliveryLocation', -7.71710000, 109.01810000)
        ->assertSet('address', '')
        ->set('name', 'Andi Pembeli')
        ->set('phone', '081234567890')
        ->set('address', 'Jl. Tes No. 1')
        ->call('submitAddress')
        ->assertSet('step', 'payment');
});
