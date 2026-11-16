<?php

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Category;
use App\Models\SellerApplication;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Filament v4 butuh panel aktif agar skema form (CreateProduct) bisa diuji.
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Role::findOrCreate('seller');
});

/*
 * Catatan Filament v4 (dipakai di ketiga test di bawah):
 * - Field ada di dalam Section, jadi kunci komponennya bukan "data.category_id"
 *   melainkan "informasi-dasar::data::section.category_id" (nama section di-slug).
 * - Data form action disimpan di "mountedActions.0.data.*", BUKAN di "data.*".
 *   Helper setFormComponentActionData() memanggil fillForm() yang mengisi form
 *   halaman (bukan form action), jadi tidak bisa dipakai di sini.
 */

/**
 * Penjual yang permohonannya sudah disetujui + tokonya sudah dibuat otomatis.
 */
function categoryCreationSeller(string $email): User
{
    $seller = User::factory()->create(['email' => $email]);
    $seller->assignRole('seller');

    $application = SellerApplication::create([
        'user_id' => $seller->id,
        'business_name' => 'Toko Kategori '.Str::random(4),
        'business_type' => 'craft',
        'business_description' => 'Toko uji kategori',
        'business_address' => 'Jl. Uji No. 1, Cilacap',
        'business_phone' => '081200000002',
        'business_email' => $email,
        'status' => 'pending',
    ]);

    // Menyetujui permohonan otomatis membuat toko berstatus approved.
    $application->update(['status' => 'approved']);

    return $seller->fresh();
}

it('penjual bisa menambah kategori baru langsung dari input kategori produk', function () {
    $seller = categoryCreationSeller('kategori-penjual@toko.test');
    $this->actingAs($seller);

    Livewire::test(CreateProduct::class)
        ->mountFormComponentAction('informasi-dasar::data::section.category_id', 'createOption')
        ->set('mountedActions.0.data.name', 'Kopi Bubuk')
        ->set('mountedActions.0.data.description', 'Kopi dan bubuk minuman')
        ->callMountedFormComponentAction()
        ->assertHasNoFormComponentActionErrors();

    $category = Category::where('slug', 'kopi-bubuk')->first();

    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Kopi Bubuk')
        ->and($category->description)->toBe('Kopi dan bubuk minuman');
});

it('memilih kategori lama saat nama yang diisi sudah ada (tanpa bikin duplikat)', function () {
    $existing = Category::create([
        'name' => 'Makanan Tradisional',
        'slug' => 'makanan-tradisional',
    ]);

    $seller = categoryCreationSeller('kategori-duplikat@toko.test');
    $this->actingAs($seller);

    $component = Livewire::test(CreateProduct::class)
        ->mountFormComponentAction('informasi-dasar::data::section.category_id', 'createOption')
        // beda huruf besar/kecil + spasi di ujung tetap dianggap sama
        ->set('mountedActions.0.data.name', '  makanan tradisional ')
        ->callMountedFormComponentAction()
        ->assertHasNoFormComponentActionErrors();

    $component->assertSet('data.category_id', $existing->id);

    expect(Category::whereRaw('LOWER(name) = ?', ['makanan tradisional'])->count())->toBe(1);
});

it('tetap bikin slug unik kalau slug hasil nama sudah terpakai', function () {
    // "Kopi-Bubuk" -> slug "kopi-bubuk" sudah terpakai, tapi namanya beda
    // sehingga bukan duplikat.
    Category::create(['name' => 'Kopi-Bubuk', 'slug' => 'kopi-bubuk']);

    $seller = categoryCreationSeller('kategori-slug@toko.test');
    $this->actingAs($seller);

    Livewire::test(CreateProduct::class)
        ->mountFormComponentAction('informasi-dasar::data::section.category_id', 'createOption')
        ->set('mountedActions.0.data.name', 'Kopi Bubuk')
        ->callMountedFormComponentAction()
        ->assertHasNoFormComponentActionErrors();

    expect(Category::where('name', 'Kopi Bubuk')->value('slug'))->toBe('kopi-bubuk-2')
        ->and(Category::count())->toBe(2);
});
