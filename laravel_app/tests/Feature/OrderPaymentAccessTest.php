<?php

use App\Livewire\Pages\OrderPaymentPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function orderViewFixtures(): array
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    // Penjual yang juga berbelanja di toko orang lain.
    $sellerBuyer = User::factory()->create();
    $sellerBuyer->assignRole('seller');
    $sellerBuyer->assignRole('buyer');

    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('seller');

    $otherShop = Shop::create([
        'user_id' => $otherSeller->id,
        'name' => 'Toko Orang Lain',
        'slug' => 'toko-lain-'.uniqid(),
        'description' => 'Toko milik penjual lain',
        'status' => 'approved',
    ]);

    $category = Category::create([
        'name' => 'Oleh-oleh',
        'slug' => 'oleh-oleh-'.uniqid(),
    ]);

    $product = Product::factory()->create([
        'shop_id' => $otherShop->id,
        'category_id' => $category->id,
        'price' => 25000,
        'stock' => 10,
        'status' => 'published',
    ]);

    $order = Order::create([
        'user_id' => $sellerBuyer->id,
        'shipping_name' => 'Penjual Pembeli',
        'shipping_phone' => '081234567890',
        'shipping_address' => 'Jl. Tes No. 1',
        'shipping_city' => 'Cilacap',
        'payment_method' => 'qris',
        'payment_status' => 'unpaid',
        'qris_type' => 'static',
        'qris_payload' => '00020101021126610014ID.CO.QRIS.WWW01189360000911234567890215ID10200000000000103UMI51440014ID.CO.QRIS.WWW0215ID1020000000000010303UMI5204581253033605802ID5910Toko  Test6007Cilacap61055000162070703A016304A1B2',
        'shipping_method' => 'pickup',
        'subtotal' => 50000,
        'shipping_cost' => 0,
        'total' => 50000,
        'status' => 'pending',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'shop_id' => $otherShop->id,
        'product_name' => $product->name,
        'shop_name' => $otherShop->name,
        'price' => 25000,
        'quantity' => 2,
        'subtotal' => 50000,
    ]);

    return compact('sellerBuyer', 'otherSeller', 'otherShop', 'order');
}

it('lets a seller open the payment page of their own order from another shop', function () {
    ['sellerBuyer' => $sellerBuyer, 'order' => $order] = orderViewFixtures();

    expect($sellerBuyer->can('view', $order))->toBeTrue();

    Livewire::actingAs($sellerBuyer)
        ->test(OrderPaymentPage::class, ['order' => $order])
        ->assertOk()
        ->assertSee($order->order_number);
});

it('still lets the shop owner verify an order that contains their product', function () {
    ['otherSeller' => $otherSeller, 'order' => $order] = orderViewFixtures();

    expect($otherSeller->can('view', $order))->toBeTrue()
        ->and($otherSeller->can('verifyPayment', $order))->toBeTrue();
});

it('does not let a buyer verify their own payment', function () {
    ['sellerBuyer' => $sellerBuyer, 'order' => $order] = orderViewFixtures();

    expect($sellerBuyer->can('verifyPayment', $order))->toBeFalse();
});

it('does not show the raw qris payload on the payment page', function () {
    ['sellerBuyer' => $sellerBuyer, 'order' => $order] = orderViewFixtures();

    Livewire::actingAs($sellerBuyer)
        ->test(OrderPaymentPage::class, ['order' => $order])
        ->assertOk()
        ->assertDontSee($order->qris_payload)
        ->assertSee('Rp 50.000');
});
