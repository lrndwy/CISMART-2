<div
    class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group cursor-pointer">
    <div class="relative">
        <img src="{{ $this->bannerUrl }}" alt="{{ $shop->name }}"
            class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300">
        <div
            class="absolute inset-0 bg-black bg-opacity-0 group-20 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
            <button wire:click="visitStore"
                class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white text-gray-900 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 flex items-center gap-2">
                <flux:icon.eye class="h-4 w-4" />
                Kunjungi Toko
            </button>
        </div>
    </div>
    <div class="p-4">
        <div class="flex items-start gap-3 mb-3">
            <img src="{{ $this->logoUrl }}" alt="{{ $shop->name }}"
                class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-green-600 transition-colors">
                    {{ $shop->name }}
                </h3>
                <div class="flex items-center gap-1 text-sm text-gray-600">
                    <flux:icon.map-pin class="h-3 w-3" />
                    <span>{{ $shop->address ?? 'Cilacap' }}</span>
                </div>
            </div>
        </div>
        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
            {{ $shop->description ?? 'Toko UMKM terpercaya di Cilacap' }}
        </p>
        <div class="space-y-2 mb-4">
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-1">
                    <flux:icon.star class="h-4 w-4 text-yellow-400 fill-current" />
                    <span class="font-medium">4.8</span>
                    <span class="text-gray-500">(125 ulasan)</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-sm text-gray-600">
                <div class="flex items-center gap-1">
                    <flux:icon.cube class="h-4 w-4" />
                    <span>{{ $shop->products_count }} produk</span>
                </div>
                <span class="text-green-600 font-medium">
                    98% respon
                </span>
            </div>
        </div> {{-- Store Badges --}}
        <div class="flex gap-2 mb-4">
            @if ($shop->status === 'approved')
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                    ✓ Terverifikasi
                </span>
            @endif
            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-medium">
                ⭐ Power Seller
            </span>
        </div> <button wire:click="visitStore"
            class="w-full bg-green-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-all duration-300 transform hover:scale-105">
            Kunjungi Toko
        </button>
    </div>
</div>
