<?php

namespace App\Livewire\Components;

use App\Models\Shop;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class StoreCard extends Component
{
    public Shop $shop;

    public function mount(Shop $shop)
    {
        $this->shop = $shop->loadCount('products');
    }

    public function render()
    {
        return view('livewire.components.store-card');
    }

    public function visitStore()
    {
        return redirect()->route('shops.show', $this->shop->slug);
    }

    public function getBannerUrlProperty()
    {
        return $this->shop->banner
            ? Storage::url($this->shop->banner)
            : 'https://picsum.photos/seed/cismart-s' . $this->shop->id . '/800/300';
    }

    public function getLogoUrlProperty()
    {
        return $this->shop->logo
            ? Storage::url($this->shop->logo)
            : 'https://picsum.photos/seed/cismart-l' . $this->shop->id . '/96/96';
    }
}
