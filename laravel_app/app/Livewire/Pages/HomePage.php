<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Shop;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class HomePage extends Component
{
    public function render()
    {
        $featuredProducts = Product::with(['shop', 'category'])
            ->published()
            ->featured()
            ->where('stock', '>', 0)
            ->take(8)
            ->get();

        $topStores = Shop::withCount('products')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $productCount = Product::published()->count();
        $shopCount = Shop::where('status', 'approved')->count();

        $benefits = [
            [
                'title' => 'Mudah & Praktis',
                'description' => 'Berbelanja produk UMKM Cilacap dari rumah dengan pengalaman yang mudah dan menyenangkan.',
                'icon' => 'cart',
            ],
            [
                'title' => 'Produk Berkualitas',
                'description' => 'Semua produk telah melalui kurasi ketat untuk memastikan kualitas terbaik bagi konsumen.',
                'icon' => 'star',
            ],
            [
                'title' => 'Dukung Ekonomi Lokal',
                'description' => 'Setiap pembelian Anda membantu mengembangkan ekonomi UMKM di Kabupaten Cilacap.',
                'icon' => 'heart',
            ],
            [
                'title' => 'Pengiriman Cepat',
                'description' => 'Jaringan logistik terintegrasi memastikan produk sampai dengan cepat dan aman.',
                'icon' => 'truck',
            ],
        ];

        return view('livewire.pages.home-page', [
            'featuredProducts' => $featuredProducts,
            'topStores' => $topStores,
            'productCount' => $productCount,
            'shopCount' => $shopCount,
            'benefits' => $benefits,
        ]);
    }



    public function selectStore($shopId)
    {
        return redirect()->route('shops.show', $shopId);
    }

    public function handleSellerRegistration()
    {
        return $this->redirect(route('register.umkm'));
    }
}
