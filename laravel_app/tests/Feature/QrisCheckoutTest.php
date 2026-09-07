<?php

use App\Livewire\Pages\CheckoutPage;
use App\Livewire\Pages\OrderPaymentPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\QrisPayload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function qrisCheckoutFixtures(): array
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko QRIS',
        'slug' => 'toko-qris-'.uniqid(),
        'description' => 'Toko uji QRIS',
        'status' => 'approved',
        'qris_static_payload' => QrisPayload::makeStaticDemo('Toko QRIS'),
        'qris_mode' => 'dynamic',
    ]);

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

it('creates a qris order with a dynamic payload and lets the buyer upload proof', function () {
    Storage::fake('public');

    ['buyer' => $buyer, 'seller' => $seller, 'shop' => $shop, 'product' => $product] = qrisCheckoutFixtures();

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

    $this->actingAs($buyer);
    session([
        'cart' => $cart,
        'checkout_items' => [$product->id],
    ]);

    Livewire::test(CheckoutPage::class)
        ->set('name', 'Andi Pembeli')
        ->set('phone', '081234567890')
        ->set('address', 'Jl. Tes No. 1')
        ->set('city', 'Cilacap')
        ->call('submitAddress')
        ->set('paymentMethod', 'qris')
        ->call('submitPayment')
        ->call('submitOrder')
        ->assertRedirect();

    $order = Order::query()->first();

    expect($order)->not->toBeNull()
        ->and($order->payment_method)->toBe('qris')
        ->and($order->qris_type)->toBe('dynamic')
        ->and($order->payment_status)->toBe('unpaid')
        ->and(QrisPayload::isValid($order->qris_payload))->toBeTrue()
        ->and(QrisPayload::parse($order->qris_payload)['01'])->toBe('12')
        ->and(QrisPayload::parse($order->qris_payload)['54'])->toBe('50000');

    Livewire::actingAs($buyer)
        ->test(OrderPaymentPage::class, ['order' => $order])
        ->set('proof', UploadedFile::fake()->image('bukti.jpg'))
        ->call('uploadProof')
        ->assertHasNoErrors();

    $order->refresh();

    expect($order->payment_status)->toBe('awaiting_verification')
        ->and($order->payment_proof_path)->not->toBeNull();

    $this->actingAs($seller);
    $order->verifyPayment();
    $order->refresh();

    expect($order->payment_status)->toBe('verified')
        ->and($order->status)->toBe('confirmed');
});

it('rejects qris checkout when the shop has no qris', function () {
    ['buyer' => $buyer, 'shop' => $shop, 'product' => $product] = qrisCheckoutFixtures();

    $shop->update(['qris_static_payload' => null]);

    $cart = [
        $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'image' => null,
            'quantity' => 1,
            'shop_name' => $shop->name,
            'shop_id' => $shop->id,
        ],
    ];

    $this->actingAs($buyer);
    session([
        'cart' => $cart,
        'checkout_items' => [$product->id],
    ]);

    Livewire::test(CheckoutPage::class)
        ->set('name', 'Andi Pembeli')
        ->set('phone', '081234567890')
        ->set('address', 'Jl. Tes No. 1')
        ->call('submitAddress')
        ->set('paymentMethod', 'qris')
        ->call('submitPayment')
        ->assertSet('step', 'payment');

    expect(Order::query()->count())->toBe(0);
});
