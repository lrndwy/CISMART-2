<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProfilePage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login', ['redirect' => request()->fullUrl()]));

            return;
        }

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login'));

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);

        $user->update($validated);

        $this->dispatch('notify', ['message' => 'Profil berhasil diperbarui.', 'type' => 'success']);
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login'));

            return;
        }

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Masukkan password saat ini.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // User casts password as "hashed", so the plain value is hashed on save.
        $user->update(['password' => $this->password]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('notify', ['message' => 'Password berhasil diubah.', 'type' => 'success']);
    }

    public function removeFavorite(int $productId): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login'));

            return;
        }

        $user->favorites()->where('product_id', $productId)->delete();

        $this->dispatch('notify', ['message' => 'Dihapus dari favorit.', 'type' => 'info']);
    }

    public function render()
    {
        $user = Auth::user();

        $favorites = $user
            ? $user->favorites()->with('product.shop')->latest()->get()->map->product->filter()
            : collect();

        return view('livewire.pages.profile-page', compact('favorites'));
    }
}
