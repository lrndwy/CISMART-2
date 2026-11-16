<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class LoginPage extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public string $redirectTo = '/';

    public function mount(): void
    {
        $redirect = request()->query('redirect', '/');

        if (is_string($redirect) && str_starts_with($redirect, url('/'))) {
            $redirect = parse_url($redirect, PHP_URL_PATH) ?: '/';
        }

        if (! is_string($redirect) || ! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/';
        }

        $this->redirectTo = $redirect;

        if (Auth::check()) {
            $this->redirect($this->redirectTo ?: '/', navigate: true);
        }
    }

    protected function throttleKey(): string
    {
        return 'login:'.Str::transliterate(Str::lower($this->email)).'|'.request()->ip();
    }

    public function login()
    {
        $this->validate();

        // Batasi percobaan login (brute force).
        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());
            $this->addError('email', "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.");

            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), 300);
            $this->addError('email', 'Email atau kata sandi salah.');

            return;
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        // Belum verifikasi email → arahkan ke halaman verifikasi.
        if (config('auth.verification.required') && ! Auth::user()->hasVerifiedEmail()) {
            return $this->redirect(route('verification.notice'), navigate: true);
        }

        if (! request()->query('redirect') && Auth::user()->isSeller()) {
            return $this->redirect(route('dashboard'), navigate: true);
        }

        return $this->redirect($this->redirectTo ?: '/', navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.login-page');
    }
}