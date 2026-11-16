<?php

use App\Livewire\Pages\RegisterPage;
use App\Livewire\Pages\RegisterUmkmPage;
use App\Models\SellerApplication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('buyer');
    $seller = Role::findOrCreate('seller');
    $seller->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('products.create', 'web'));
});

it('registers a buyer and logs them in', function () {
    Livewire::test(RegisterPage::class)
        ->set('name', 'Pembeli Baru')
        ->set('email', 'pembeli@baru.test')
        ->set('phone', '081234567890')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('register')
        ->assertRedirect('/');

    $this->assertAuthenticated();

    $user = User::where('email', 'pembeli@baru.test')->firstOrFail();
    expect($user->hasRole('buyer'))->toBeTrue();
    expect(Hash::check('rahasia123', $user->password))->toBeTrue();
});

it('rejects a duplicate buyer email', function () {
    User::factory()->create(['email' => 'sudah@ada.test']);

    Livewire::test(RegisterPage::class)
        ->set('name', 'Orang Lain')
        ->set('email', 'sudah@ada.test')
        ->set('phone', '081234567890')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('register')
        ->assertHasErrors('email');

    expect(User::where('email', 'sudah@ada.test')->count())->toBe(1);
});

it('rejects a short password', function () {
    Livewire::test(RegisterPage::class)
        ->set('name', 'Pembeli Baru')
        ->set('email', 'pendek@baru.test')
        ->set('phone', '081234567890')
        ->set('password', 'abc123')
        ->set('password_confirmation', 'abc123')
        ->call('register')
        ->assertHasErrors('password');
});

it('registers a guest as seller application and shows pending status', function () {
    Storage::fake('public');

    Livewire::test(RegisterUmkmPage::class)
        ->set('name', 'Pemilik Toko')
        ->set('email', 'toko@baru.test')
        ->set('phone', '081234567890')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->set('business_name', 'Toko Baru')
        ->set('business_type', 'craft')
        ->set('business_description', 'Kerajinan khas Cilacap.')
        ->set('business_address', 'Jl. Diponegoro No. 1, Cilacap')
        ->set('business_phone', '081234567890')
        ->set('business_email', 'toko@baru.test')
        ->set('documents', [UploadedFile::fake()->image('ktp.jpg')])
        // Plan-01: NIB dan persetujuan Perjanjian Penjual kini wajib.
        ->set('nib_document', UploadedFile::fake()->create('nib.pdf', 200, 'application/pdf'))
        ->set('agreement_accepted', true)
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertAuthenticated();

    $user = User::where('email', 'toko@baru.test')->firstOrFail();
    expect($user->hasRole('buyer'))->toBeTrue();
    // Role seller baru diberikan saat admin menyetujui permohonan (F-13).
    expect($user->hasRole('seller'))->toBeFalse();
    expect($user->can('products.create'))->toBeFalse();

    $application = SellerApplication::where('user_id', $user->id)->firstOrFail();
    expect($application->status)->toBe('pending');
    expect($application->business_type)->toBe('craft');
    expect($application->business_documents)->toHaveCount(1);
    Storage::disk('public')->assertExists($application->business_documents[0]);
});

it('lets a rejected applicant resubmit and resets status to pending', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->assignRole('buyer');

    $application = $user->sellerApplication()->create([
        'business_name' => 'Toko Lama',
        'business_type' => 'food',
        'business_description' => 'Deskripsi lama.',
        'business_address' => 'Alamat lama',
        'business_phone' => '081234567890',
        'business_email' => $user->email,
        'business_documents' => ['seller-documents/lama.pdf'],
        'status' => 'rejected',
        'rejection_reason' => 'Dokumen kurang jelas.',
    ]);

    $this->actingAs($user);

    Livewire::test(RegisterUmkmPage::class)
        ->set('business_name', 'Toko Lama')
        ->set('business_type', 'food')
        ->set('business_description', 'Deskripsi diperbaiki.')
        ->set('business_address', 'Alamat lama')
        ->set('business_phone', '081234567890')
        ->set('business_email', $user->email)
        // Plan-01: permohonan ini belum punya NIB dan belum ada persetujuan.
        ->set('nib_document', UploadedFile::fake()->create('nib.pdf', 200, 'application/pdf'))
        ->set('agreement_accepted', true)
        ->call('submit')
        ->assertHasNoErrors();

    $application->refresh();
    expect($application->status)->toBe('pending');
    expect($application->rejection_reason)->toBeNull();
    expect($application->business_documents)->toContain('seller-documents/lama.pdf');
});

it('hides the form when the applicant already has a pending application', function () {
    $user = User::factory()->create();
    $user->assignRole('buyer');

    $user->sellerApplication()->create([
        'business_name' => 'Toko Pending',
        'business_type' => 'souvenir',
        'business_description' => 'Deskripsi.',
        'business_address' => 'Alamat',
        'business_phone' => '081234567890',
        'business_email' => $user->email,
        'status' => 'pending',
    ]);

    $this->actingAs($user);

    Livewire::test(RegisterUmkmPage::class)
        ->assertDontSee('Kirim Permohonan')
        ->assertSee('Permohonan sedang ditinjau');
});

it('redirects an authenticated user away from the buyer registration page', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('buyer');

    $this->actingAs($buyer);

    Livewire::test(RegisterPage::class)
        ->assertRedirect('/');
});
