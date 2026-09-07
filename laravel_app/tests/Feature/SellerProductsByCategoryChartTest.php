<?php

use App\Filament\Widgets\SellerProductsByCategoryChart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('loads seller product counts by category without sqlite having error', function () {
    Role::findOrCreate('seller');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Test',
        'slug' => 'toko-test',
        'description' => 'Toko untuk tes chart',
        'status' => 'approved',
    ]);

    $withProducts = Category::create([
        'name' => 'Makanan',
        'slug' => 'makanan',
    ]);

    Category::create([
        'name' => 'Kosong',
        'slug' => 'kosong',
    ]);

    Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => $withProducts->id,
        'status' => 'published',
    ]);

    $this->actingAs($seller);

    $widget = app(SellerProductsByCategoryChart::class);
    $data = (new \ReflectionMethod($widget, 'getData'))->invoke($widget);

    expect($data['labels'])->toBe(['Makanan'])
        ->and($data['datasets'][0]['data'])->toBe([1]);
});
