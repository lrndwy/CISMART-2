<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
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

    public function login()
    {
        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], true)) {
            $this->addError('email', 'Email atau kata sandi salah.');

            return;
        }

        session()->regenerate();

        return $this->redirect($this->redirectTo ?: '/', navigate: true);
    }

    public function render()
    {
        return view('livewire.pages.login-page');
    }
}
