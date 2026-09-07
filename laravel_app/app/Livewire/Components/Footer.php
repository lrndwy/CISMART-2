<?php

namespace App\Livewire\Components;

use Livewire\Component;

class Footer extends Component
{
    public $email = '';

    public function render()
    {
        return view('livewire.components.footer');
    }

    public function navigate($page)
    {
        $routes = [
            'home' => 'home',
            'products' => 'products.index',
            'shops' => 'shops.index',
            'community' => 'community.index',
            'blog' => 'blog.index',
            'dashboard' => 'dashboard',
        ];

        if (isset($routes[$page])) {
            return redirect()->route($routes[$page]);
        }
    }

    public function subscribeNewsletter()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        // Save newsletter subscription
        // Newsletter::create(['email' => $this->email]);

        $this->dispatch('notify', [
            'message' => 'Terima kasih! Anda telah berlangganan newsletter kami.',
            'type' => 'success'
        ]);

        $this->email = '';
    }
}
