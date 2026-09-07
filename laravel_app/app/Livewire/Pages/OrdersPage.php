<?php

namespace App\Livewire\Pages;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrdersPage extends Component
{
    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login', ['redirect' => request()->fullUrl()]));
        }
    }

    public function render()
    {
        $orders = Auth::check()
            ? Order::query()->where('user_id', Auth::id())->latest()->get()
            : collect();

        return view('livewire.pages.orders-page', compact('orders'));
    }
}
