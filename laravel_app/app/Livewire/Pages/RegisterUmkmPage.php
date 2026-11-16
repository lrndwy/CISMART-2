<?php

namespace App\Livewire\Pages;

use App\Models\AgreementAcceptance;
use App\Models\SellerApplication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class RegisterUmkmPage extends Component
{
    use WithFileUploads;

    public ?SellerApplication $application = null;

    // Akun (hanya dipakai saat guest)
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Bisnis
    public string $business_name = '';

    public string $business_type = '';

    public string $business_description = '';

    public string $business_address = '';

    public string $business_phone = '';

    public string $business_email = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $documents = [];

    // Izin Usaha (NIB) — satu file, terpisah dari dokumen pendukung
    public $nib_document = null;

    // Persetujuan Perjanjian Penjual
    public bool $agreement_accepted = false;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user) {
            $this->application = $user->sellerApplication;
            $this->business_email = $this->application?->business_email ?: $user->email;

            if ($this->application) {
                $this->business_name = $this->application->business_name;
                $this->business_type = $this->application->business_type;
                $this->business_description = $this->application->business_description;
                $this->business_address = $this->application->business_address;
                $this->business_phone = $this->application->business_phone;
            }
        }
    }

    public function hasStoredDocuments(): bool
    {
        return ! empty($this->application?->business_documents);
    }

    public function hasStoredNib(): bool
    {
        return filled($this->application?->nib_document_path);
    }

    /**
     * Versi Perjanjian Penjual yang berlaku, dibaca dari config/legal.php supaya
     * halaman, label checkbox, dan baris persetujuan tidak pernah berbeda.
     */
    public function sellerAgreementVersion(): string
    {
        return (string) config('legal.seller_agreement.version');
    }

    public function hasAcceptedCurrentAgreement(): bool
    {
        $user = Auth::user();

        return $user !== null
            && $user->hasAcceptedDocument(AgreementAcceptance::SELLER_AGREEMENT, $this->sellerAgreementVersion());
    }

    /**
     * Persetujuan yang sudah tercatat (untuk keterangan di form), atau null bila
     * user belum pernah menyetujui versi yang berlaku.
     */
    public function currentAgreementAcceptance(): ?AgreementAcceptance
    {
        return Auth::user()?->agreementAcceptances()
            ->where('document_type', AgreementAcceptance::SELLER_AGREEMENT)
            ->where('version', $this->sellerAgreementVersion())
            ->first();
    }

    protected function rules(): array
    {
        $rules = [
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'in:food,craft,souvenir,fashion,agriculture,technology,service,other'],
            'business_description' => ['required', 'string', 'max:2000'],
            'business_address' => ['required', 'string', 'max:255'],
            'business_phone' => ['required', 'string', 'max:20'],
            'business_email' => ['required', 'email', 'max:255'],
        ];

        if (! Auth::check()) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255', 'unique:users,email'];
            $rules['phone'] = ['required', 'string', 'max:20'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $rules['documents'] = $this->hasStoredDocuments()
            ? ['nullable', 'array', 'max:3']
            : ['required', 'array', 'min:1', 'max:3'];
        $rules['documents.*'] = ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];

        // Izin usaha wajib sampai ada file tersimpan; pengajuan ulang tidak
        // memaksa mengunggah ulang (spec plan-01 BL-004).
        $nibRules = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
        $rules['nib_document'] = $this->hasStoredNib() ? ['nullable', ...$nibRules] : ['required', ...$nibRules];

        // Checkbox hanya wajib bila versi yang berlaku belum pernah disetujui.
        $rules['agreement_accepted'] = $this->hasAcceptedCurrentAgreement() ? ['nullable'] : ['accepted'];

        return $rules;
    }

    /**
     * Pesan galat berbahasa Indonesia untuk aturan yang ditambahkan plan-01.
     */
    protected function messages(): array
    {
        $version = $this->sellerAgreementVersion();

        return [
            'agreement_accepted.accepted' => "Kamu harus menyetujui Perjanjian Penjual versi {$version} sebelum mengirim permohonan.",
            'nib_document.required' => 'Unggah dokumen Izin Usaha (NIB) dalam format PDF atau gambar.',
            'nib_document.file' => 'File NIB gagal diunggah. Coba pilih ulang filenya.',
            'nib_document.mimes' => 'File NIB harus berformat PDF, JPG, atau PNG.',
            'nib_document.max' => 'Ukuran file NIB maksimal 5 MB.',
        ];
    }

    public function submit()
    {
        $this->validate();

        $rateKey = 'seller-application:'.(Auth::id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            $this->addError('business_name', "Terlalu banyak pengiriman permohonan. Coba lagi dalam {$seconds} detik.");

            return;
        }

        RateLimiter::hit($rateKey, 600);

        // Berkas ditulis lebih dulu, transaksi database menyusul. Kalau transaksi
        // gagal, file yang terlanjur ditulis dibersihkan supaya tidak yatim.
        $oldNibPath = $this->application?->nib_document_path;
        $storedPaths = collect($this->documents)
            ->map(fn ($file) => $file->store('seller-documents', 'public'))
            ->all();
        $newNibPath = $this->nib_document?->store('seller-documents/nib', 'public');

        $createdUser = false;

        try {
            // Persetujuan kontrak dan permohonan ditulis dalam satu transaksi:
            // permohonan yang tersimpan tanpa bukti persetujuan tidak boleh ada
            // (spec plan-01 BL-003).
            $user = DB::transaction(function () use ($storedPaths, $newNibPath, &$createdUser) {
                $user = Auth::user();

                if (! $user) {
                    $user = User::create([
                        'name' => $this->name,
                        'email' => $this->email,
                        'phone' => $this->phone,
                        'password' => $this->password,
                    ]);

                    $user->assignRole('buyer');

                    Auth::login($user);
                    session()->regenerate();

                    $createdUser = true;
                }

                // Role `seller` baru diberikan saat admin menyetujui permohonan
                // (SellerApplication::provisionShop()) — bukan saat submit.

                $storedDocuments = $this->application?->business_documents ?? [];
                $documents = array_values(array_merge($storedDocuments, $storedPaths));

                $attributes = [
                    'business_name' => $this->business_name,
                    'business_type' => $this->business_type,
                    'business_description' => $this->business_description,
                    'business_address' => $this->business_address,
                    'business_phone' => $this->business_phone,
                    'business_email' => $this->business_email,
                    'business_documents' => $documents,
                    // Pengajuan ulang tanpa file baru mempertahankan NIB lama.
                    'nib_document_path' => $newNibPath ?: $this->application?->nib_document_path,
                    'status' => 'pending',
                    'rejection_reason' => null,
                    'approved_at' => null,
                    'approved_by' => null,
                ];

                if ($this->application) {
                    $this->application->update($attributes);
                } else {
                    $this->application = $user->sellerApplication()->create($attributes);
                }

                $version = $this->sellerAgreementVersion();

                if (! $user->hasAcceptedDocument(AgreementAcceptance::SELLER_AGREEMENT, $version)) {
                    AgreementAcceptance::record($user, AgreementAcceptance::SELLER_AGREEMENT, $version, request());
                }

                return $user;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete(array_values(array_filter(array_merge($storedPaths, [$newNibPath]))));

            throw $exception;
        }

        if ($createdUser && config('auth.verification.required')) {
            $user->sendEmailVerificationNotification();
        }

        // NIB lama baru dihapus setelah transaksi sukses dan hanya bila diganti.
        if ($newNibPath && $oldNibPath && $oldNibPath !== $newNibPath) {
            Storage::disk('public')->delete($oldNibPath);
        }

        $this->application->refresh();
        $this->reset('documents', 'nib_document', 'agreement_accepted');

        $this->dispatch('notify', [
            'message' => 'Permohonan UMKM terkirim. Tim CISMART meninjau maksimal 1x24 jam.',
            'type' => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.pages.register-umkm-page');
    }
}
