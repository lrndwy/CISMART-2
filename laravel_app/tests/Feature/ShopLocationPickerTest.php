<?php

use App\Filament\Resources\Shops\Pages\EditShop;
use App\Models\Shop;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('renders the shop location picker on the admin edit page', function () {
    Role::findOrCreate('admin');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $shop = Shop::create([
        'user_id' => $admin->id,
        'name' => 'Toko Peta',
        'slug' => 'toko-peta-'.uniqid(),
        'description' => 'Toko uji peta',
        'status' => 'approved',
        'latitude' => -7.71810000,
        'longitude' => 109.01810000,
        'delivery_enabled' =>true,
        'delivery_unit_meters' => 100,
        'delivery_rate' => 2000,
    ]);

    $this->actingAs($admin);

    Livewire::test(EditShop::class, ['record' => $shop->getRouteKey()])
        ->assertOk()
        ->assertSee('Lokasi Toko')
        ->assertSee('Cara menentukan lokasi toko')
        ->assertSee('Lokasi Saya')
        ->assertSee('Lokasi sudah ditandai',false)
        ->assertSee('shop-map',false)
        ->assertSee('-7.7181',false);
});
