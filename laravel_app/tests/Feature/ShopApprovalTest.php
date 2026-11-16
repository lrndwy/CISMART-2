<?php

use App\Filament\Resources\Shops\Pages\CreateShop;
use App\Livewire\Pages\ProductsPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\SellerApplication;
use App\Models\Shop;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Filament v4 butuh panel aktif agar skema form (CreateShop) bisa diuji.
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');
});

function shopOwner(string $email): User
{
    $seller = User::factory()->create(['email' => $email]);
    $seller->assignRole('seller');

    return $seller;
}

function shopWith(string $status, User $owner, string $name): Shop
{
    return Shop::create([
        'user_id' => $owner->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(6),
        'description' => 'Toko uji',
        'status' => $status,
    ]);
}

function publishedProduct(Shop $shop, string $name): Product
{
    return Product::factory()->create([
        'shop_id' => $shop->id,
        'category_id' => Category::create(['name' => 'Oleh-oleh '.Str::random(4), 'slug' => 'kategori-'.Str::random(8)])->id,
        'name' => $name,
        'slug' => Str::slug($name).'-'.Str::random(6),
        'status' => 'published',
        'stock' => 5,
    ]);
}

it('hides published products whose shop is not approved', function () {
    $approvedShop = shopWith('approved', shopOwner('approved@toko.test'), 'Toko Disetujui');
    $pendingShop = shopWith('pending', shopOwner('pending@toko.test'), 'Toko Menunggu');

    $visible = publishedProduct($approvedShop, 'Produk Tampil');
    $hidden = publishedProduct($pendingShop, 'Produk Tersembunyi');

    Livewire::test(ProductsPage::class)
        ->assertSee($visible->name)
        ->assertDontSee($hidden->name);

    $this->get('/products/'.$hidden->slug)->assertNotFound();
    $this->get('/products/'.$visible->slug)->assertOk();
});

it('approves the applicant shops when the seller application is approved', function () {
    $seller = shopOwner('cascade@toko.test');
    $shop = shopWith('pending', $seller, 'Toko Cascade');

    $application = SellerApplication::create([
        'user_id' => $seller->id,
        'business_name' => 'Toko Cascade',
        'business_type' => 'craft',
        'business_description' => 'Deskripsi toko',
        'business_address' => 'Jl. Uji No. 1',
        'business_phone' => '081234567890',
        'business_email' => $seller->email,
        'status' => 'pending',
    ]);

    $application->update(['status' => 'approved']);

    expect($shop->fresh()->status)->toBe('approved');
});

it('menolak penjual tanpa permohonan disetujui membuat toko manual', function () {
    $seller = shopOwner('createshop@toko.test');
    $this->actingAs($seller);

    // Toko dibuat otomatis saat approve; penjual pending tidak boleh membuat sendiri.
    expect(\App\Filament\Resources\ShopResource::canCreate())->toBeFalse();
    expect(\Illuminate\Support\Facades\Gate::forUser($seller)->allows('create', Shop::class))->toBeFalse();

    expect(Shop::count())->toBe(0);
});

it('membuat toko sebagai approved setelah permohonan disetujui', function () {
    $seller = shopOwner('approvedshop@toko.test');

    SellerApplication::create([
        'user_id' => $seller->id,
        'business_name' => 'Toko Sudah Disetujui',
        'business_type' => 'food',
        'business_description' => 'Deskripsi toko',
        'business_address' => 'Jl. Uji No. 2',
        'business_phone' => '081234567891',
        'business_email' => $seller->email,
        'status' => 'approved',
    ]);

    $this->actingAs($seller);

    // Catatan: fillForm() Filament v4 tidak menulis state di harness ini,
    // jadi state form diisi langsung lewat set('data.*').
    Livewire::test(CreateShop::class)
        ->set('data.name', 'Toko Kedua')
        ->set('data.slug', 'toko-kedua-uji')
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Shop::where('slug', 'toko-kedua-uji')->firstOrFail()->status)->toBe('approved');
});
