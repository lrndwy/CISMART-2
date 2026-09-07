<!-- resources/views/livewire/components/product-card.blade.php -->
<div
    class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
    <div class="relative overflow-hidden rounded-t-lg">
        <img src="{{ $this->imageUrl }}" alt="{{ $product->name }}"
            class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
        <div
            class="absolute inset-0 group-hover:bg-opacity-10 transition-all duration-300 flex items-center justify-center">
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex gap-2">
                <button wire:click="viewProduct"
                    class="bg-white text-gray-900 p-2 rounded-full hover:bg-gray-100 transition-colors"
                    title="Lihat Detail Produk">
                    <flux:icon.eye class="h-4 w-4" />
                </button>
                <button wire:click="viewShop"
                    class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition-colors"
                    title="Lihat Toko">
                    <flux:icon.building-storefront class="h-4 w-4" />
                </button>
                <button wire:click="addToCart"
                    class="bg-green-600 text-white p-2 rounded-full hover:bg-green-700 transition-colors"
                    title="Tambah ke Keranjang" wire:loading.attr="disabled">
                    <flux:icon.shopping-cart class="h-4 w-4" />
                </button>
            </div>
        </div>

        {{-- Stock Badge --}}
        @if ($product->stock > 0 && $product->stock < 10)
            <div class="absolute top-2 right-2 bg-orange-500 text-white px-2 py-1 rounded text-xs font-medium">
                Stok: {{ $product->stock }}
            </div>
        @elseif($product->stock === 0)
            <div class="absolute top-2 right-2 bg-gray-500 text-white px-2 py-1 rounded text-xs font-medium">
                Habis
            </div>
        @endif

        @if ($product->is_featured)
            <div class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-medium">
                ⭐ Featured
            </div>
        @endif
    </div>

    <div class="p-4">
        <h3 wire:click="viewProduct"
            class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-green-600 transition-colors cursor-pointer"
            title="Klik untuk melihat detail produk">
            {{ $product->name }}
        </h3>

        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
            {{ $product->description }}
        </p>

        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1">
                <flux:icon.star class="h-4 w-4 text-yellow-400 fill-current" />
                <span class="text-sm text-gray-600">4.5</span>
                <span class="text-gray-400">•</span>
                <span class="text-sm text-gray-500">Terjual</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-3">
            <div>
                <span class="text-xl font-bold text-green-600">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <button wire:click="viewShop"
            class="text-sm text-gray-600 hover:text-green-600 transition-colors mb-3 flex items-center gap-1 cursor-pointer"
            title="Klik untuk melihat toko">
            <flux:icon.building-storefront class="h-3 w-3" />
            {{ $product->shop->name }}
        </button>

        <button wire:click="addToCart" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed"
            @disabled($product->stock === 0)
            class="{{ $product->stock === 0
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-green-600 text-white hover:bg-green-700 transform hover:scale-105' }}
                w-full py-2 px-4 rounded-lg font-medium transition-all duration-300 mb-2">
            <span wire:loading.remove wire:target="addToCart">
                {{ $product->stock === 0 ? 'Stok Habis' : ($addedToCart ? 'Ditambahkan! ✓' : 'Tambah ke Keranjang') }}
            </span>
            <span wire:loading wire:target="addToCart">
                Menambahkan...
            </span>
        </button>

        @if ($product->stock > 0)
            <button wire:click="orderViaWhatsApp"
                class="w-full py-2 px-4 rounded-lg font-medium transition-all duration-300 bg-green-500 text-white hover:bg-green-600 transform hover:scale-105 flex items-center justify-center gap-2">
                💬 Pesan via WhatsApp
            </button>
        @endif
    </div>
</div>
