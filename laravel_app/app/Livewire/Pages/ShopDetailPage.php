<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\Shop;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ShopDetailPage extends Component
{
    public ?Shop $shop = null;
    public string $activeTab = 'products';

    public function mount($slug)
    {
        $this->shop = Shop::where('status', 'approved')
            ->where('slug', $slug)
            ->withCount(['products' => function ($q) {
                $q->where('status', 'published');
            }])
            ->firstOrFail();
    }

    public function setActiveTab($tab)
    {
        if (in_array($tab, ['products', 'about', 'reviews'])) {
            $this->activeTab = $tab;
        }
    }

    public function contactViaWhatsApp()
    {
        $message = "🏪 *KONTAK UMKM DARI CISMART*\n\n" .
            "Halo {$this->shop->name}!\n\n" .
            "Saya tertarik dengan produk-produk di toko Anda. Mohon informasi lebih lanjut tentang:\n" .
            "- Katalog produk terbaru\n" .
            "- Harga dan ketersediaan\n" .
            "- Cara pemesanan\n\n" .
            "📍 Lokasi: {$this->shop->address}\n" .
            "⭐ Rating: {$this->shop->rating}/5\n\n" .
            "Terima kasih! 🙏";

        $whatsappNumber = $this->shop->phone ?? '6288225292279';
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($message);

        return redirect()->to($whatsappUrl);
    }

    public function getShopProductsProperty()
    {
        return Product::where('shop_id', $this->shop->id)
            ->where('status', 'published')
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getBannerUrlProperty()
    {
        if ($this->shop->banner) {
            if (filter_var($this->shop->banner, FILTER_VALIDATE_URL)) {
                return $this->shop->banner;
            }
            return Storage::url($this->shop->banner);
        }
        return asset('images/shop-banner-placeholder.jpg');
    }

    public function getLogoUrlProperty()
    {
        if ($this->shop->logo) {
            if (filter_var($this->shop->logo, FILTER_VALIDATE_URL)) {
                return $this->shop->logo;
            }
            return Storage::url($this->shop->logo);
        }
        return asset('images/shop-logo-placeholder.jpg');
    }

    public function getAchievementsProperty()
    {
        return [
            [
                'icon' => '🏆',
                'label' => 'Top Seller',
                'description' => 'Penjual terbaik bulan ini'
            ],
            [
                'icon' => '⭐',
                'label' => 'Rating Tinggi',
                'description' => ($this->shop->rating ?? 0) . '/5 dari ' . ($this->shop->total_reviews ?? 0) . ' ulasan'
            ],
            [
                'icon' => '🚚',
                'label' => 'Pengiriman Cepat',
                'description' => 'Rata-rata kirim dalam 1 hari'
            ],
            [
                'icon' => '💬',
                'label' => 'Responsif',
                'description' => ($this->shop->response_rate ?? 0) . '% tingkat respons'
            ],
        ];
    }

    public function getMockReviewsProperty()
    {
        return [
            [
                'id' => '1',
                'user' => 'Sari Wijaya',
                'rating' => 5,
                'comment' => 'Produk berkualitas tinggi dan pengiriman sangat cepat. Pelayanan ramah dan profesional.',
                'date' => '2024-01-15',
                'product' => 'Batik Cilacap Motif Tradisional'
            ],
            [
                'id' => '2',
                'user' => 'Budi Santoso',
                'rating' => 5,
                'comment' => 'Sudah beberapa kali beli di sini, selalu puas. Kualitas produk konsisten.',
                'date' => '2024-01-10',
                'product' => 'Kerajinan Bambu Set'
            ],
            [
                'id' => '3',
                'user' => 'Maya Indah',
                'rating' => 4,
                'comment' => 'Produk bagus, packaging rapi. Hanya saja pengiriman agak lama.',
                'date' => '2024-01-05',
                'product' => 'Makanan Ringan Khas Cilacap'
            ],
        ];
    }

    public function render()
    {
        return view('livewire.pages.shop-detail-page', [
            'shopProducts' => $this->shopProducts,
            'bannerUrl' => $this->bannerUrl,
            'logoUrl' => $this->logoUrl,
            'achievements' => $this->achievements,
            'mockReviews' => $this->mockReviews,
        ]);
    }
}
