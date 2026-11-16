<?php

namespace App\Livewire\Pages;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RegisterPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect('/', navigate: true);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function register()
    {
        $this->validate();

        // Cegah pembuatan akun massal dari satu IP.
        $throttleKey = 'register:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Terlalu banyak pendaftaran dari jaringan ini. Coba lagi dalam {$seconds} detik.");

            return;
        }

        RateLimiter::hit($throttleKey, 600);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $this->password,
        ]);

        $user->assignRole('buyer');

        Auth::login($user);
        session()->regenerate();

        $user->sendEmailVerificationNotification();

        $this->dispatch('notify', ['message' => 'Akun pembeli berhasil dibuat. Cek email untuk verifikasi.', 'type' => 'success']);

        if (config('auth.verification.required')) {
            return $this->redirect(route('verification.notice'), navigate: true);
        }

        return $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.register-page');
    }
}
