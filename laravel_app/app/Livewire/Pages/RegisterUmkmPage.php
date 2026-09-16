<?php

namespace App\Livewire\Pages;

use App\Models\SellerApplication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

        return $rules;
    }

    public function submit()
    {
        $this->validate();

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
        }

        if (! $user->hasRole('seller')) {
            $user->assignRole('seller');
        }

        $stored = $this->application?->business_documents ?? [];
        $newPaths = collect($this->documents)
            ->map(fn ($file) => $file->store('seller-documents', 'public'))
            ->all();
        $documents = array_values(array_merge($stored, $newPaths));

        $attributes = [
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'business_description' => $this->business_description,
            'business_address' => $this->business_address,
            'business_phone' => $this->business_phone,
            'business_email' => $this->business_email,
            'business_documents' => $documents,
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

        $this->application->refresh();
        $this->reset('documents');

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
