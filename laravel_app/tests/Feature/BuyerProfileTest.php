<?php

use App\Livewire\Pages\ProfilePage;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function profileFixtures(): array
{
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');

    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Profil',
        'slug' => 'toko-profil-'.uniqid(),
        'description' => 'Toko uji profil',
        'status' => 'approved',
    ]);

    $product = Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Oleh-oleh', 'slug' => 'oleh-oleh-'.uniqid()])->id,
        'slug' => 'produk-profil-'.uniqid(),
        'status' => 'published',
        'stock' => 5,
    ]);

    $buyer = User::factory()->create(['password' => 'password']);
    $buyer->assignRole('buyer');

    return compact('seller', 'buyer', 'shop', 'product');
}

it('redirects a guest to login', function () {
    Livewire::test(ProfilePage::class)->assertRedirect();
});

it('updates the profile information', function () {
    ['buyer' => $buyer] = profileFixtures();

    Livewire::actingAs($buyer)->test(ProfilePage::class)
        ->set('name', 'Nadia Pramesti')
        ->set('email', 'nadia@example.test')
        ->set('phone', '081298765432')
        ->set('address', 'Jl. Melati 12, Cilacap')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $buyer->refresh();
    expect($buyer->name)->toBe('Nadia Pramesti')
        ->and($buyer->email)->toBe('nadia@example.test')
        ->and($buyer->phone)->toBe('081298765432')
        ->and($buyer->address)->toBe('Jl. Melati 12, Cilacap');
});

it('rejects an email already used by another account', function () {
    ['buyer' => $buyer] = profileFixtures();
    User::factory()->create(['email' => 'taken@example.test']);

    Livewire::actingAs($buyer)->test(ProfilePage::class)
        ->set('email', 'taken@example.test')
        ->call('updateProfile')
        ->assertHasErrors(['email']);

    expect($buyer->fresh()->email)->not->toBe('taken@example.test');
});

it('changes the password when the current password matches', function () {
    ['buyer' => $buyer] = profileFixtures();

    Livewire::actingAs($buyer)->test(ProfilePage::class)
        ->set('current_password', 'password')
        ->set('password', 'rahasia-baru-2026')
        ->set('password_confirmation', 'rahasia-baru-2026')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('rahasia-baru-2026', $buyer->fresh()->password))->toBeTrue();
});

it('rejects a password change when the current password is wrong', function () {
    ['buyer' => $buyer] = profileFixtures();

    Livewire::actingAs($buyer)->test(ProfilePage::class)
        ->set('current_password', 'salah')
        ->set('password', 'rahasia-baru-2026')
        ->set('password_confirmation', 'rahasia-baru-2026')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);

    expect(Hash::check('password', $buyer->fresh()->password))->toBeTrue();
});

it('lists favorited products and removes one', function () {
    ['buyer' => $buyer, 'product' => $product] = profileFixtures();

    Favorite::create(['user_id' => $buyer->id, 'product_id' => $product->id]);

    Livewire::actingAs($buyer)->test(ProfilePage::class)
        ->assertSee($product->name)
        ->assertDontSee('Belum ada produk favorit');

    Livewire::actingAs($buyer)->test(ProfilePage::class)
        ->call('removeFavorite', $product->id)
        ->assertSee('Belum ada produk favorit');

    expect(Favorite::count())->toBe(0);
});
