<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ProductDetailPage extends Component
{
    public ?Product $product = null;

    public int $quantity = 1;

    public int $selectedImage = 0;

    public bool $showReviews = false;

    public function mount($slug)
    {
        // Always query by slug since route parameter is named {slug}
        $this->product = Product::with('shop', 'category')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();
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

    public function getWhatsAppMessage()
    {
        $shopLocation = $this->product->shop->address ?? 'Tidak tersedia';
        $discountedPrice = $this->product->discount
            ? floor($this->product->price * (1 - $this->product->discount / 100))
            : $this->product->price;

        return urlencode(
            "🛍️ *MINAT PRODUK DARI CISMART*\n\n".
                "Halo, saya tertarik dengan produk:\n\n".
                "📦 *{$this->product->name}*\n".
                '💰 Harga: Rp '.number_format($discountedPrice, 0, ',', '.')."\n".
                "📊 Jumlah: {$this->quantity} pcs\n".
                '💵 Total: Rp '.number_format($discountedPrice * $this->quantity, 0, ',', '.')."\n".
                "🏪 Toko: {$this->product->shop->name}\n".
                "📍 Lokasi: {$shopLocation}\n\n".
                "Apakah produk ini masih tersedia? Mohon informasi lebih lanjut untuk pemesanan.\n\n".
                'Terima kasih! 🙏'
        );
    }

    public function orderViaWhatsApp()
    {
        if ($this->product->stock === 0) {
            $this->dispatch('notify', type: 'error', message: 'Stok habis');

            return;
        }

        $whatsappNumber = $this->product->shop->phone ?? '6288225292279';
        $message = $this->getWhatsAppMessage();
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=($message)";

        return redirect()->to($whatsappUrl);
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
            'mockReviews' => $this->getMockReviews(),
            'averageRating' => $this->averageRating,
        ]);
    }

    private function getMockReviews()
    {
        return [
            [
                'id' => '1',
                'user' => 'Sari Wijaya',
                'rating' => 5,
                'comment' => 'Produk sangat bagus, kualitas sesuai dengan harga. Pengiriman cepat dan packaging rapi.',
                'date' => '2024-01-15',
                'helpful' => 12,
            ],
            [
                'id' => '2',
                'user' => 'Budi Santoso',
                'rating' => 4,
                'comment' => 'Bagus, tapi warnanya sedikit berbeda dari foto. Overall puas dengan pembelian ini.',
                'date' => '2024-01-10',
                'helpful' => 8,
            ],
            [
                'id' => '3',
                'user' => 'Maya Indah',
                'rating' => 5,
                'comment' => 'Sudah beli berkali-kali, selalu puas. Kualitas konsisten dan pelayanan ramah.',
                'date' => '2024-01-05',
                'helpful' => 15,
            ],
        ];
    }
}
