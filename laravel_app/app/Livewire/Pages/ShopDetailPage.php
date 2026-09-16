<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ShopDetailPage extends Component
{
    public ?Shop $shop = null;
    public string $activeTab = 'products';
    public int $rating = 0;
    public string $comment = '';

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

    public function getShopProductsProperty()
    {
        return Product::where('shop_id', $this->shop->id)
            ->published()
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
        return 'https://picsum.photos/seed/cismart-s' . $this->shop->id . '/1600/400';
    }

    public function getLogoUrlProperty()
    {
        if ($this->shop->logo) {
            if (filter_var($this->shop->logo, FILTER_VALIDATE_URL)) {
                return $this->shop->logo;
            }
            return Storage::url($this->shop->logo);
        }
        return 'https://picsum.photos/seed/cismart-l' . $this->shop->id . '/240/240';
    }

    public function getAchievementsProperty()
    {
        return [
            [
                'icon' => 'trophy',
                'label' => 'Toko Terverifikasi',
                'description' => 'Didata dan disetujui tim CISMART'
            ],
            [
                'icon' => 'star',
                'label' => 'Rating Tinggi',
                'description' => ($this->shop->rating ?? 0) . '/5 dari ' . ($this->shop->total_reviews ?? 0) . ' ulasan'
            ],
            [
                'icon' => 'truck',
                'label' => 'Layanan Kirim',
                'description' => $this->shop->hasDelivery() ? 'Kirim ke lokasi tersedia' : 'Ambil di toko'
            ],
            [
                'icon' => 'chat',
                'label' => 'Responsif',
                'description' => ($this->shop->response_rate ?? 0) . '% tingkat respons'
            ],
        ];
    }

    public function setRating(int $rating): void
    {
        if ($rating >= 1 && $rating <= 5) {
            $this->rating = $rating;
        }
    }

    public function submitReview(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login', ['redirect' => route('shops.show', $this->shop->slug)]));

            return;
        }

        if (! $user->hasOrderedFrom($this->shop)) {
            $this->dispatch('notify', [
                'message' => 'Ulasan hanya untuk pembeli yang pernah bertransaksi di toko ini.',
                'type' => 'error',
            ]);

            return;
        }

        if ($this->shop->reviews()->where('user_id', $user->id)->exists()) {
            $this->dispatch('notify', [
                'message' => 'Kamu sudah memberi ulasan untuk toko ini.',
                'type' => 'error',
            ]);

            return;
        }

        $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rating.required' => 'Pilih rating bintang dulu.',
            'rating.between' => 'Rating harus 1 sampai 5 bintang.',
            'comment.required' => 'Tulis ulasanmu dulu.',
            'comment.min' => 'Ulasan minimal 10 karakter.',
            'comment.max' => 'Ulasan maksimal 1000 karakter.',
        ]);

        $this->shop->reviews()->create([
            'user_id' => $user->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        $this->shop->refreshRating();
        $this->reset('rating', 'comment');

        $this->dispatch('notify', ['message' => 'Ulasan terkirim. Terima kasih!', 'type' => 'success']);
    }

    public function getReviewsProperty()
    {
        return $this->shop->reviews()->with('user')->latest()->limit(20)->get();
    }

    public function getMyReviewProperty(): ?Review
    {
        $user = Auth::user();

        return $user ? $this->shop->reviews()->where('user_id', $user->id)->first() : null;
    }

    public function getCanReviewProperty(): bool
    {
        $user = Auth::user();

        return $user !== null
            && ! $this->shop->reviews()->where('user_id', $user->id)->exists()
            && $user->hasOrderedFrom($this->shop);
    }

    public function render()
    {
        return view('livewire.pages.shop-detail-page', [
            'shopProducts' => $this->shopProducts,
            'bannerUrl' => $this->bannerUrl,
            'logoUrl' => $this->logoUrl,
            'achievements' => $this->achievements,
            'reviews' => $this->reviews,
            'canReview' => $this->canReview,
            'myReview' => $this->myReview,
        ]);
    }
}
