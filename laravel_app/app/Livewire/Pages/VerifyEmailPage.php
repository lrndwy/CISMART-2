<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class VerifyEmailPage extends Component
{
    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirect(route('orders.index'));
        }
    }

    public function resend(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $key = 'verify-email:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->dispatch('notify', [
                'message' => "Tunggu {$seconds} detik sebelum meminta email lagi.",
                'type' => 'error',
            ]);

            return;
        }

        RateLimiter::hit($key, 300);

        $user->sendEmailVerificationNotification();

        $this->dispatch('notify', [
            'message' => 'Email verifikasi dikirim ulang. Cek kotak masuk (atau log mail di server).',
            'type' => 'success',
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('login'));
    }

    public function render()
    {
        return view('livewire.pages.verify-email-page');
    }
}