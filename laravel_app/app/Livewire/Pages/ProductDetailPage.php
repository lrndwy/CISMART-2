<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ProductDetailPage extends Component
{
    public ?Product $product = null;

    public int $quantity = 1;

    public int $selectedImage = 0;

    public bool $showReviews = false;

    public bool $isFavorited = false;

    // Form ulasan produk
    public int $rating = 5;

    public string $comment = '';

    public function mount($slug)
    {
        $this->product = Product::with('shop', 'category')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->isFavorited = $this->product->isFavoritedBy(Auth::user());
    }

    public function incrementQuantity()
    {
        $max = max(1, (int) $this->availableStock());

        if ($this->quantity < $max) {
            $this->quantity++;
        }
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function selectImage($index)
    {
        if (
            is_array($this->product->images) &&
            isset($this->product->images[$index])
        ) {
            $this->selectedImage = $index;
        }
    }

    public function toggleReviews()
    {
        $this->showReviews = ! $this->showReviews;
    }

    /**
     * Sisa stok yang masih bisa dimasukkan ke keranjang.
     */
    protected function availableStock(): int
    {
        $product = Product::query()->published()->find($this->product->id);

        if (! $product) {
            return 0;
        }

        $inCart = (int) (session('cart.'.$product->id.'.quantity') ?? 0);

        return max(0, (int) $product->stock - $inCart);
    }

    public function addToCart()
    {
        $product = Product::query()->with('shop')->published()->find($this->product->id);

        if (! $product) {
            $this->dispatch('notify', type: 'error', message: 'Produk sudah tidak tersedia');

            return;
        }

        if ($product->stock <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Stok habis');

            return;
        }

        $cart = session()->get('cart', []);
        $existing = (int) ($cart[$product->id]['quantity'] ?? 0);
        $requested = min($this->quantity, max(0, (int) $product->stock - $existing));

        if ($requested <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Jumlah di keranjang sudah mencapai stok tersedia');

            return;
        }

        $cart[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'image' => $product->first_image,
            'quantity' => $existing + $requested,
            'shop_name' => $product->shop?->name ?? '',
            'shop_id' => $product->shop_id,
        ];

        session()->put('cart', $cart);

        $this->dispatch('notify', type: 'success', message: "{$requested} {$product->name} berhasil ditambahkan ke keranjang!");
    }

    public function toggleFavorite()
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login', ['redirect' => route('products.show', $this->product->slug)]));

            return;
        }

        $existing = $this->product->favorites()->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            $this->isFavorited = false;
            $this->dispatch('notify', ['message' => 'Dihapus dari favorit.', 'type' => 'info']);

            return;
        }

        $this->product->favorites()->create(['user_id' => $user->id]);
        $this->isFavorited = true;
        $this->dispatch('notify', ['message' => 'Ditambahkan ke favorit.', 'type' => 'success']);
    }

    public function submitReview(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->redirect(route('login', ['redirect' => route('products.show', $this->product->slug)]));

            return;
        }

        if (! $user->hasDeliveredProduct($this->product)) {
            $this->dispatch('notify', [
                'message' => 'Ulasan hanya untuk produk yang sudah kamu terima.',
                'type' => 'error',
            ]);

            return;
        }

        if ($this->product->reviews()->where('user_id', $user->id)->exists()) {
            $this->dispatch('notify', [
                'message' => 'Kamu sudah mengulas produk ini.',
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

        $this->product->reviews()->create([
            'shop_id' => $this->product->shop_id,
            'product_id' => $this->product->id,
            'order_item_id' => $user->deliveredOrderItemFor($this->product)?->id,
            'user_id' => $user->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        $this->reset('rating', 'comment');
        $this->product->refresh();

        $this->dispatch('notify', ['message' => 'Ulasan terkirim. Terima kasih!', 'type' => 'success']);
    }

    public function getFavoriteCountProperty(): int
    {
        return $this->product->favorites()->count();
    }

    public function getReviewsProperty()
    {
        return $this->product->reviews()->with('user')->latest()->limit(20)->get();
    }

    public function getCanReviewProperty(): bool
    {
        $user = Auth::user();

        return $user !== null
            && $user->hasDeliveredProduct($this->product)
            && ! $this->product->reviews()->where('user_id', $user->id)->exists();
    }

    public function getMyReviewProperty(): ?Review
    {
        $user = Auth::user();

        return $user ? $this->product->reviews()->where('user_id', $user->id)->first() : null;
    }

    public function getProductImagesProperty()
    {
        $images = [];

        // Handle different product image formats
        if (is_array($this->product->images) && ! empty($this->product->images)) {
            foreach ($this->product->images as $image) {
                if (is_string($image)) {
                    // Check if it's a full URL or storage path
                    if (filter_var($image, FILTER_VALIDATE_URL)) {
                        $images[] = $image;
                    } else {
                        $images[] = Storage::url($image);
                    }
                } elseif (is_array($image) && isset($image['image'])) {
                    $images[] = Storage::url($image['image']);
                }
            }
        }

        // Fallback to first_image if images array is empty
        if (empty($images) && $this->product->first_image) {
            if (filter_var($this->product->first_image, FILTER_VALIDATE_URL)) {
                $images[] = $this->product->first_image;
            } else {
                $images[] = Storage::url($this->product->first_image);
            }
        }

        // Final fallback to placeholder
        if (empty($images)) {
            $images[] = asset('images/placeholder.png');
        }

        return $images;
    }

    public function getAverageRatingProperty()
    {
        return (float) ($this->product->rating ?? 0);
    }

    public function render()
    {
        return view('livewire.pages.product-detail-page', [
            'productImages' => $this->productImages,
            'averageRating' => $this->averageRating,
            'favoriteCount' => $this->favoriteCount,
            'reviews' => $this->reviews,
            'canReview' => $this->canReview,
            'myReview' => $this->myReview,
        ]);
    }
}