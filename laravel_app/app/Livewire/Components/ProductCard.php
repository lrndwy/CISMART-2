<?php

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ProductCard extends Component
{
    public Product $product;

    public $addedToCart = false;

    public function mount(Product $product)
    {
        $this->product = $product->load(['shop', 'category']);
    }

    public function render()
    {
        return view('livewire.components.product-card');
    }

    public function addToCart()
    {
        if (! $this->product->isInStock()) {
            $this->dispatch('notify', [
                'message' => 'Produk tidak tersedia!',
                'type' => 'error',
            ]);

            return;
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$this->product->id])) {
            $cart[$this->product->id]['quantity']++;
        } else {
            $cart[$this->product->id] = [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'price' => $this->product->price,
                'image' => $this->product->first_image,
                'quantity' => 1,
                'shop_name' => $this->product->shop->name,
                'shop_id' => $this->product->shop_id,
            ];
        }

        session()->put('cart', $cart);

        $this->addedToCart = true;
        $this->dispatch('cart-updated');

        $this->dispatch('notify', [
            'message' => 'Produk ditambahkan ke keranjang!',
            'type' => 'success',
        ]);

        // Reset button state
        $this->dispatch('$refresh');
    }

    public function orderViaWhatsApp()
    {
        $message = "🛍️ *MINAT PRODUK DARI CISMART*\n\n".
            "Halo, saya tertarik dengan produk:\n\n".
            "📦 *{$this->product->name}*\n".
            '💰 Harga: Rp '.number_format($this->product->price, 0, ',', '.')."\n".
            "🏪 Toko: {$this->product->shop->name}\n".
            "📍 Lokasi: {$this->product->shop->address}\n\n".
            "Apakah produk ini masih tersedia? Mohon informasi lebih lanjut untuk pemesanan.\n\n".
            'Terima kasih! 🙏';

        $whatsappNumber = $this->product->shop->phone ?? '6288225292279';
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=".urlencode($message);

        return redirect()->to($whatsappUrl);
    }

    public function viewShop()
    {
        return redirect()->route('shops.show', $this->product->shop->slug);
    }

    public function viewProduct()
    {
        return redirect()->route('products.show', $this->product->slug);
    }

    public function getImageUrlProperty()
    {
        // Handle different product image formats
        if ($this->product->first_image) {
            // If first_image is a full URL, return it directly
            if (filter_var($this->product->first_image, FILTER_VALIDATE_URL)) {
                return $this->product->first_image;
            }

            // Otherwise, use Storage URL
            return Storage::url($this->product->first_image);
        }

        // Fallback: try to get from images array/collection
        if (! empty($this->product->images)) {
            if (is_array($this->product->images)) {
                $firstImage = reset($this->product->images);
                if (is_string($firstImage)) {
                    return filter_var($firstImage, FILTER_VALIDATE_URL)
                        ? $firstImage
                        : Storage::url($firstImage);
                }
                if (is_array($firstImage) && isset($firstImage['image'])) {
                    return Storage::url($firstImage['image']);
                }
            } elseif ($this->product->images instanceof \Illuminate\Support\Collection) {
                $first = $this->product->images->first();
                if ($first) {
                    if (is_string($first)) {
                        return filter_var($first, FILTER_VALIDATE_URL)
                            ? $first
                            : Storage::url($first);
                    }
                    if (is_object($first) && isset($first->image)) {
                        return Storage::url($first->image);
                    }
                }
            }
        }

        // Final fallback
        return asset('images/placeholder.jpg');
    }
}
