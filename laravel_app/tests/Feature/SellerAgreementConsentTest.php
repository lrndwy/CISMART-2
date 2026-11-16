<?php

use App\Livewire\Pages\RegisterUmkmPage;
use App\Models\AgreementAcceptance;
use App\Models\SellerApplication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('buyer');
    Role::findOrCreate('seller');
    Storage::fake('public');
});

function consentForm(array $overrides = []): array
{
    return array_merge([
        'name' => 'Pemilik Usaha',
        'email' => 'pemilik-'.uniqid().'@toko.test',
        'phone' => '081200000001',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'business_name' => 'Toko Rotan Cilacap',
        'business_type' => 'craft',
        'business_description' => 'Kerajinan rotan khas Cilacap',
        'business_address' => 'Jl. Rotan No. 8, Cilacap',
        'business_phone' => '081200000002',
        'business_email' => 'rotan@toko.test',
    ], $overrides);
}

it('menolak permohonan tanpa centang persetujuan', function () {
    Livewire::test(RegisterUmkmPage::class)
        ->set(consentForm())
        ->set('documents', [UploadedFile::fake()->image('ktp.jpg')])
        ->set('nib_document', UploadedFile::fake()->create('nib.pdf', 200, 'application/pdf'))
        ->call('submit')
        ->assertHasErrors('agreement_accepted');

    expect(SellerApplication::count())->toBe(0)
        ->and(AgreementAcceptance::count())->toBe(0)
        ->and(User::count())->toBe(0);
});

it('mencatat persetujuan saat permohonan dikirim', function () {
    Livewire::test(RegisterUmkmPage::class)
        ->set(consentForm())
        ->set('documents', [UploadedFile::fake()->image('ktp.jpg')])
        ->set('nib_document', UploadedFile::fake()->create('nib.pdf', 200, 'application/pdf'))
        ->set('agreement_accepted', true)
        ->call('submit')
        ->assertHasNoErrors();

    $application = SellerApplication::sole();
    $acceptance = AgreementAcceptance::sole();

    expect($application->status)->toBe('pending')
        ->and($acceptance->user_id)->toBe($application->user_id)
        ->and($acceptance->document_type)->toBe(AgreementAcceptance::SELLER_AGREEMENT)
        ->and($acceptance->version)->toBe(config('legal.seller_agreement.version'))
        ->and($acceptance->accepted_at)->not->toBeNull()
        ->and($acceptance->ip_address)->not->toBeEmpty();
});

it('tidak membuat baris persetujuan kedua saat pengajuan ulang pada versi yang sama', function () {
    $user = User::factory()->create();
    $user->assignRole('buyer');
    SellerApplication::create([
        'user_id' => $user->id,
        'business_name' => 'Toko Rotan Cilacap',
        'business_type' => 'craft',
        'business_description' => 'Kerajinan rotan',
        'business_address' => 'Jl. Rotan No. 8',
        'business_phone' => '081200000002',
        'business_email' => 'rotan@toko.test',
        'business_documents' => ['seller-documents/ktp.jpg'],
        'nib_document_path' => 'seller-documents/nib/nib.pdf',
        'status' => 'rejected',
        'rejection_reason' => 'Alamat kurang lengkap',
    ]);

    AgreementAcceptance::record($user, AgreementAcceptance::SELLER_AGREEMENT, config('legal.seller_agreement.version'), request());

    $this->actingAs($user);

    Livewire::test(RegisterUmkmPage::class)
        ->set(consentForm(['email' => $user->email]))
        ->set('agreement_accepted', false)
        ->call('submit')
        ->assertHasNoErrors();

    expect(AgreementAcceptance::count())->toBe(1)
        ->and($user->fresh()->sellerApplication->status)->toBe('pending');
});

it('meminta persetujuan lagi ketika versi kontrak naik', function () {
    $user = User::factory()->create();
    $user->assignRole('buyer');
    SellerApplication::create([
        'user_id' => $user->id,
        'business_name' => 'Toko Rotan Cilacap',
        'business_type' => 'craft',
        'business_description' => 'Kerajinan rotan',
        'business_address' => 'Jl. Rotan No. 8',
        'business_phone' => '081200000002',
        'business_email' => 'rotan@toko.test',
        'business_documents' => ['seller-documents/ktp.jpg'],
        'nib_document_path' => 'seller-documents/nib/nib.pdf',
        'status' => 'rejected',
    ]);

    AgreementAcceptance::record($user, AgreementAcceptance::SELLER_AGREEMENT, '1.0', request());

    config(['legal.seller_agreement.version' => '2.0']);
    $this->actingAs($user);

    Livewire::test(RegisterUmkmPage::class)
        ->set(consentForm(['email' => $user->email]))
        ->set('agreement_accepted', true)
        ->call('submit')
        ->assertHasNoErrors();

    expect(AgreementAcceptance::count())->toBe(2)
        ->and($user->fresh()->hasAcceptedDocument(AgreementAcceptance::SELLER_AGREEMENT, '1.0'))->toBeTrue()
        ->and($user->fresh()->hasAcceptedDocument(AgreementAcceptance::SELLER_AGREEMENT, '2.0'))->toBeTrue();
});