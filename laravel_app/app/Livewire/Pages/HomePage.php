<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
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

        $stats = [
            ['icon' => 'shopping-bag', 'label' => 'UMKM Terdaftar', 'value' => Shop::where('status', 'approved')->count().'+'],
            ['icon' => 'users', 'label' => 'Pembeli Aktif', 'value' => '10,000+'],
            ['icon' => 'truck', 'label' => 'Produk Terjual', 'value' => '50,000+'],
            ['icon' => 'shield-check', 'label' => 'Transaksi Aman', 'value' => '100%'],
        ];

        $benefits = [
            [
                'title' => 'Mudah & Praktis',
                'description' => 'Berbelanja produk UMKM Cilacap dari rumah dengan pengalaman yang mudah dan menyenangkan.',
                'icon' => '🛒',
            ],
            [
                'title' => 'Produk Berkualitas',
                'description' => 'Semua produk telah melalui kurasi ketat untuk memastikan kualitas terbaik bagi konsumen.',
                'icon' => '⭐',
            ],
            [
                'title' => 'Dukung Ekonomi Lokal',
                'description' => 'Setiap pembelian Anda membantu mengembangkan ekonomi UMKM di Kabupaten Cilacap.',
                'icon' => '💝',
            ],
            [
                'title' => 'Pengiriman Cepat',
                'description' => 'Jaringan logistik terintegrasi memastikan produk sampai dengan cepat dan aman.',
                'icon' => '🚚',
            ],
        ];

        return view('livewire.pages.home-page', [
            'featuredProducts' => $featuredProducts,
            'topStores' => $topStores,
            'stats' => $stats,
            'benefits' => $benefits,
        ]);
    }

    public function navigateToProducts()
    {
        return redirect()->route('products.index');
    }

    public function navigateToCommunity()
    {
        return redirect()->route('community.index');
    }

    public function navigateToBlog()
    {
        return redirect()->route('blog.index');
    }

    public function selectStore($shopId)
    {
        return redirect()->route('shops.show', $shopId);
    }

    public function handleSellerRegistration()
    {
        if (Auth::check()) {
            return $this->redirect('/admin');
        }

        return $this->redirect('/admin/login');
    }
}
