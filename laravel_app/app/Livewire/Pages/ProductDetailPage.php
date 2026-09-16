<?php

namespace App\Livewire\Pages;

use App\Models\Product;
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

    public function mount($slug)
    {
        // Always query by slug since route parameter is named {slug}
        $this->product = Product::with('shop', 'category')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $this->isFavorited = $this->product->isFavoritedBy(Auth::user());
    }

    public function incrementQuantity()
    {
        if ($this->quantity < $this->product->stock) {
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

    public function addToCart()
    {
        if ($this->product->stock === 0) {
            $this->dispatch('notify', type: 'error', message: 'Stok habis');

            return;
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$this->product->id])) {
            $cart[$this->product->id]['quantity'] += $this->quantity;
        } else {
            $cart[$this->product->id] = [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'image' => $this->product->first_image,
                'quantity' => $this->quantity,
                'shop_name' => $this->product->shop->name,
                'shop_id' => $this->product->shop_id,
            ];
        }

        session()->put('cart', $cart);

        $this->dispatch('notify', type: 'success', message: "{$this->quantity} {$this->product->name} berhasil ditambahkan ke keranjang!");
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

    public function getFavoriteCountProperty(): int
    {
        return $this->product->favorites()->count();
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
        return $this->product->rating ?? 0;
    }

    public function render()
    {
        return view('livewire.pages.product-detail-page', [
            'productImages' => $this->productImages,
            'averageRating' => $this->averageRating,
            'favoriteCount' => $this->favoriteCount,
        ]);
    }
}
