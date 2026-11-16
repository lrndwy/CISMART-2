<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ForgotPasswordPage extends Component
{
    public string $email = '';

    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function sendResetLink(): void
    {
        $this->validate();

        $key = 'forgot-password:'.Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik.");

            return;
        }

        RateLimiter::hit($key, 600);

        $status = Password::sendResetLink(['email' => $this->email]);

        // Jangan bocorkan apakah email terdaftar atau tidak.
        if ($status === Password::RESET_THROTTLED) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Permintaan baru saja dikirim. Tunggu {$seconds} detik.");

            return;
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.pages.forgot-password-page');
    }
}