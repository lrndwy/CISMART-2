<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    public $searchQuery = '';

    public $showMobileMenu = false;

    public function render()
    {
        // Hitung total kuantitas (bukan jumlah baris produk) agar badge akurat.
        $cartItemCount = (int) array_sum(array_column(session()->get('cart', []), 'quantity'));

        return view('livewire.components.header', [
            'cartItemCount' => $cartItemCount,
        ]);
    }

    #[On('cart-updated')]
    public function refreshCart()
    {
        // Trigger re-render to update cart count
    }

    public function search()
    {
        if (trim($this->searchQuery)) {
            session()->put('search_query', $this->searchQuery);

            return redirect()->route('products.index', ['search' => $this->searchQuery]);
        }
    }

    public function navigate($page)
    {
        $this->showMobileMenu = false;

        $routes = [
            'home' => 'home',
            'products' => 'products.index',
            'shops' => 'shops.index',
            'blog' => 'blog.index',
            'cart' => 'cart.index',
            'orders' => 'orders.index',
            'profile' => 'profile',
            'dashboard' => 'dashboard',
        ];

        if (isset($routes[$page])) {
            return redirect()->route($routes[$page]);
        }
    }

    public function showAuthModal()
    {
        return redirect()->route('login');
    }

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home');
    }

    public function toggleMobileMenu()
    {
        $this->showMobileMenu = ! $this->showMobileMenu;
    }
}
