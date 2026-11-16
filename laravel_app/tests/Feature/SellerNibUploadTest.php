<?php

use App\Filament\Resources\SellerApplicationResource;
use App\Filament\Resources\SellerApplications\Pages\ViewSellerApplication;
use App\Livewire\Pages\RegisterUmkmPage;
use App\Models\SellerApplication;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('buyer');
    Role::findOrCreate('seller');
    Role::findOrCreate('admin');
    // Policy permohonan memeriksa permission ini; tanpa barisnya Filament
    // melempar PermissionDoesNotExist saat merender halaman view.
    Permission::findOrCreate('seller-applications.view');
    Permission::findOrCreate('seller-applications.manage');
    Storage::fake('public');
});

function nibForm(array $overrides = []): array
{
    return array_merge([
        'name' => 'Pemilik Usaha',
        'email' => 'nib-'.uniqid().'@toko.test',
        'phone' => '081200000001',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'business_name' => 'Toko Rotan Cilacap',
        'business_type' => 'craft',
        'business_description' => 'Kerajinan rotan khas Cilacap',
        'business_address' => 'Jl. Rotan No. 8, Cilacap',
        'business_phone' => '081200000002',
        'business_email' => 'rotan@toko.test',
        'agreement_accepted' => true,
    ], $overrides);
}

function nibApplication(User $user, array $overrides = []): SellerApplication
{
    return SellerApplication::create(array_merge([
        'user_id' => $user->id,
        'business_name' => 'Toko Rotan Cilacap',
        'business_type' => 'craft',
        'business_description' => 'Kerajinan rotan',
        'business_address' => 'Jl. Rotan No. 8',
        'business_phone' => '081200000002',
        'business_email' => 'rotan@toko.test',
        'business_documents' => ['seller-documents/ktp.jpg'],
        'status' => 'pending',
    ], $overrides));
}

it('mewajibkan file NIB pada permohonan baru', function () {
    Livewire::test(RegisterUmkmPage::class)
        ->set(nibForm())
        ->set('documents', [UploadedFile::fake()->image('ktp.jpg')])
        ->call('submit')
        ->assertHasErrors('nib_document');

    expect(SellerApplication::count())->toBe(0);
});

it('menolak NIB dengan tipe file yang tidak diizinkan', function () {
    Livewire::test(RegisterUmkmPage::class)
        ->set(nibForm())
        ->set('documents', [UploadedFile::fake()->image('ktp.jpg')])
        ->set('nib_document', UploadedFile::fake()->create('nib.exe', 100, 'application/x-msdownload'))
        ->call('submit')
        ->assertHasErrors('nib_document');

    expect(SellerApplication::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles('seller-documents/nib'))->toBeEmpty();
});

it('menolak NIB yang lebih besar dari 5 MB', function () {
    Livewire::test(RegisterUmkmPage::class)
        ->set(nibForm())
        ->set('documents', [UploadedFile::fake()->image('ktp.jpg')])
        ->set('nib_document', UploadedFile::fake()->create('nib.pdf', 6 * 1024, 'application/pdf'))
        ->call('submit')
        ->assertHasErrors('nib_document');

    expect(SellerApplication::count())->toBe(0)
        ->and(Storage::disk('public')->allFiles('seller-documents/nib'))->toBeEmpty();
});

it('menyimpan NIB di disk public dan mencatat path-nya', function () {
    Livewire::test(RegisterUmkmPage::class)
        ->set(nibForm())
        ->set('documents', [UploadedFile::fake()->image('ktp.jpg')])
        ->set('nib_document', UploadedFile::fake()->create('nib.pdf', 500, 'application/pdf'))
        ->call('submit')
        ->assertHasNoErrors();

    $application = SellerApplication::sole();

    expect($application->nib_document_path)->toStartWith('seller-documents/nib/')
        ->and($application->hasNib())->toBeTrue();

    Storage::disk('public')->assertExists($application->nib_document_path);
});

it('mempertahankan NIB lama saat pengajuan ulang tanpa file baru', function () {
    $user = User::factory()->create();
    $user->assignRole('buyer');
    $application = nibApplication($user, [
        'status' => 'rejected',
        'nib_document_path' => 'seller-documents/nib/nib-lama.pdf',
    ]);

    $this->actingAs($user);

    Livewire::test(RegisterUmkmPage::class)
        ->set(nibForm(['email' => $user->email]))
        ->call('submit')
        ->assertHasNoErrors();

    expect($application->fresh()->nib_document_path)->toBe('seller-documents/nib/nib-lama.pdf');
});

it('menampilkan tautan NIB di halaman status milik pemohon', function () {
    $user = User::factory()->create();
    $user->assignRole('buyer');
    nibApplication($user, ['nib_document_path' => 'seller-documents/nib/nib-pemilik.pdf']);

    $this->actingAs($user)
        ->get('/register/umkm')
        ->assertOk()
        ->assertSee('Izin Usaha (NIB)')
        ->assertSee('nib-pemilik.pdf');
});

it('tidak menampilkan NIB pengguna lain', function () {
    $owner = User::factory()->create();
    $owner->assignRole('buyer');
    nibApplication($owner, ['nib_document_path' => 'seller-documents/nib/nib-rahasia.pdf']);

    $other = User::factory()->create();
    $other->assignRole('buyer');

    $this->actingAs($other)
        ->get('/register/umkm')
        ->assertOk()
        ->assertDontSee('nib-rahasia.pdf');
});

it('menampilkan NIB di halaman view panel admin', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $applicant = User::factory()->create();
    $applicant->assignRole('buyer');
    $application = nibApplication($applicant, [
        'nib_document_path' => 'seller-documents/nib/nib-admin.pdf',
    ]);

    $this->actingAs($admin);

    Livewire::test(ViewSellerApplication::class, ['record' => $application->getKey()])
        ->assertSuccessful()
        ->assertSee('Izin Usaha (NIB)');

    expect(SellerApplicationResource::getSlug())->toBe('seller-applications');
});

it('menampilkan keadaan kosong di panel untuk permohonan tanpa NIB', function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $applicant = User::factory()->create();
    $applicant->assignRole('buyer');
    $application = nibApplication($applicant);

    $this->actingAs($admin);

    Livewire::test(ViewSellerApplication::class, ['record' => $application->getKey()])
        ->assertSuccessful();
});