<?php

use App\Filament\Resources\ShopResource;
use App\Models\SellerApplication;
use App\Models\Shop;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('seller');
    Role::findOrCreate('buyer');
});

function provisioningApplication(User $user, array $overrides = []): SellerApplication
{
    return SellerApplication::create(array_merge([
        'user_id' => $user->id,
        'business_name' => 'Toko Rotan Cilacap',
        'business_type' => 'craft',
        'business_description' => 'Kerajinan rotan khas Cilacap',
        'business_address' => 'Jl. Rotan No. 8, Cilacap',
        'business_phone' => '081200000001',
        'business_email' => 'rotan@toko.test',
        'status' => 'pending',
    ], $overrides));
}

it('membuat toko otomatis dari data permohonan saat disetujui', function () {
    $applicant = User::factory()->create(['email' => 'pemohon@toko.test']);
    $application = provisioningApplication($applicant);

    expect(Shop::count())->toBe(0);

    $application->update(['status' => 'approved']);

    $shop = $applicant->shops()->firstOrFail();

    expect($shop->status)->toBe('approved')
        ->and($shop->name)->toBe('Toko Rotan Cilacap')
        ->and($shop->slug)->toBe('toko-rotan-cilacap')
        ->and($shop->description)->toBe('Kerajinan rotan khas Cilacap')
        ->and($shop->phone)->toBe('081200000001')
        ->and($shop->email)->toBe('rotan@toko.test')
        ->and($shop->address)->toBe('Jl. Rotan No. 8, Cilacap');

    expect($applicant->fresh()->hasRole('seller'))->toBeTrue();
});

it('menyimpan approver dan tidak membuat toko ganda saat approve dipanggil dua kali', function () {
    $admin = User::factory()->create();
    $applicant = User::factory()->create(['email' => 'pemohon2@toko.test']);
    $application = provisioningApplication($applicant);

    $application->approve($admin);
    $application->approve($admin);

    expect(Shop::count())->toBe(1)
        ->and($application->fresh()->approved_by)->toBe($admin->id)
        ->and($application->fresh()->approved_at)->not->toBeNull();
});

it('tidak menimpa toko yang sudah dibuat penjual, hanya mengaktifkan dan melengkapi', function () {
    $seller = User::factory()->create(['email' => 'pemohon3@toko.test']);
    $seller->assignRole('seller');

    $shop = Shop::create([
        'user_id' => $seller->id,
        'name' => 'Nama Pilihan Penjual',
        'slug' => 'nama-pilihan-penjual',
        'description' => null,
        'status' => 'pending',
    ]);

    $application = provisioningApplication($seller);
    $application->update(['status' => 'approved']);

    $shop->refresh();

    expect(Shop::count())->toBe(1)
        ->and($shop->status)->toBe('approved')
        ->and($shop->name)->toBe('Nama Pilihan Penjual')
        ->and($shop->description)->toBe('Kerajinan rotan khas Cilacap')
        ->and($shop->phone)->toBe('081200000001');
});

it('memberi slug unik bila nama toko sudah dipakai', function () {
    $existingOwner = User::factory()->create();
    Shop::create([
        'user_id' => $existingOwner->id,
        'name' => 'Toko Rotan Cilacap',
        'slug' => 'toko-rotan-cilacap',
        'status' => 'approved',
    ]);

    $applicant = User::factory()->create(['email' => 'pemohon4@toko.test']);
    provisioningApplication($applicant)->update(['status' => 'approved']);

    expect($applicant->shops()->firstOrFail()->slug)->toBe('toko-rotan-cilacap-2');
});

it('tidak menampilkan tambah toko bagi penjual yang sudah punya toko', function () {
    $seller = User::factory()->create(['email' => 'pemohon5@toko.test']);
    $seller->assignRole('seller');

    // Penjual tanpa permohonan disetujui tidak boleh membuat toko manual.
    $this->actingAs($seller);
    expect(ShopResource::canCreate())->toBeFalse();

    // Permohonan disetujui langsung (bukan lewat transition) belum membuat toko,
    // jadi tombol tambah toko masih tersedia sebagai jalan pemulihan.
    provisioningApplication($seller, ['status' => 'approved']);
    expect(ShopResource::canCreate())->toBeTrue();

    // Setelah toko ada (lewat approve/transition), tombolnya hilang lagi.
    Shop::create([
        'user_id' => $seller->id,
        'name' => 'Toko Pemulihan',
        'slug' => 'toko-pemulihan',
        'status' => 'approved',
    ]);

    expect(ShopResource::canCreate())->toBeFalse();
});