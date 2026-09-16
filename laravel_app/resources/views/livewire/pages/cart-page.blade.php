@php
    $cartImage = function ($item, $productId) {
        $img = data_get($item, 'image');
        if (! $img) {
            return 'https://picsum.photos/seed/cismart-p' . $productId . '/160/160';
        }
        return filter_var($img, FILTER_VALIDATE_URL) ? $img : Storage::url($img);
    };
@endphp
<div class="bg-stone-50 py-6 sm:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (empty($cart))
            {{-- Empty cart --}}
            <div class="text-center py-16">
                <svg class="h-16 w-16 text-stone-300 mx-auto mb-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-stone-900 mb-4">Keranjang Belanja Kosong</h2>
                <p class="text-stone-500 mb-8">
                    Belum ada produk yang ditambahkan ke keranjang belanja Anda
                </p>
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2 bg-green-700 text-white px-8 py-3 rounded-lg font-medium text-sm hover:bg-green-800 active:scale-[0.98] transition">
                    Mulai Belanja
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        @else
            {{-- Header --}}
            <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8">
                <a href="{{ route('products.index') }}" aria-label="Kembali ke katalog" class="p-2.5 hover:bg-stone-100 rounded-lg transition-colors">
                    <svg class="h-5 w-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-stone-900">Keranjang Belanja</h1>
                    <p class="text-sm sm:text-base text-stone-500">{{ $totalItems }} produk dalam keranjang</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                {{-- Cart Items --}}
                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-stone-200/70 bg-white shadow-[0_1px_2px_rgba(28,25,23,0.05)] overflow-hidden">
                        {{-- Select All Header --}}
                        <div class="p-4 border-b border-stone-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="selectAll" wire:change="toggleSelectAll"
                                    class="rounded accent-green-700" />
                                <span class="ml-3 text-sm font-medium text-stone-900">
                                    Pilih Semua ({{ count($cart) }} produk)
                                </span>
                            </label>
                        </div>

                        {{-- Cart Items List --}}
                        <div class="divide-y divide-stone-200">
                            @foreach ($cart as $productId => $item)
                                <div wire:key="cart-item-{{ $productId }}" class="p-3 sm:p-4 flex gap-3 sm:gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model.live="selectedItems"
                                            value="{{ $productId }}"
                                            wire:change="toggleSelectItem({{ $productId }})"
                                            class="h-5 w-5 rounded accent-green-700" />
                                    </div>

                                    <img src="{{ $cartImage($item, $productId) }}"
                                        alt="{{ data_get($item, 'name', 'Produk') }}"
                                        class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg shrink-0" />

                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm sm:text-base font-semibold text-stone-900 mb-1 line-clamp-2">
                                            {{ data_get($item, 'name', 'Produk') }}
                                        </h3>
                                        <p class="text-xs sm:text-sm text-stone-500 mb-3">
                                            {{ data_get($item, 'shop_name', '-') }}
                                        </p>

                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="inline-flex items-center self-start rounded-lg border border-stone-300 bg-white">
                                                <button
                                                    wire:click="updateQuantity({{ $productId }}, {{ max(1, $item['quantity'] - 1) }})"
                                                    {{ $item['quantity'] <= 1 ? 'disabled' : '' }}
                                                    aria-label="Kurangi jumlah"
                                                    class="p-3 text-stone-600 transition hover:text-green-700 active:scale-[0.92] disabled:cursor-not-allowed disabled:text-stone-300">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                                    </svg>
                                                </button>
                                                <span class="w-10 text-center text-sm font-semibold tabular-nums text-stone-900">
                                                    {{ $item['quantity'] }}
                                                </span>
                                                <button
                                                    wire:click="updateQuantity({{ $productId }}, {{ $item['quantity'] + 1 }})"
                                                    aria-label="Tambah jumlah"
                                                    class="p-3 text-stone-600 transition hover:text-green-700 active:scale-[0.92]">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="text-left sm:text-right">
                                                <div class="text-sm sm:text-base font-bold text-stone-900">
                                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                </div>
                                                <div class="text-xs sm:text-sm text-stone-500">
                                                    Rp {{ number_format($item['price'], 0, ',', '.') }} per item
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button wire:click="removeFromCart({{ $productId }})"
                                        wire:confirm="Hapus produk dari keranjang?"
                                        aria-label="Hapus dari keranjang"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors self-start">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="rounded-xl border border-stone-200/70 bg-white p-5 sm:p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)] lg:sticky lg:top-24">
                        <h3 class="text-base font-semibold text-stone-900 mb-4">Ringkasan Pesanan</h3>

                        <div class="space-y-3 mb-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-stone-500">Produk terpilih</span>
                                <span class="font-medium text-stone-900">{{ $selectedCount }} item</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-500">Subtotal</span>
                                <span class="font-medium text-stone-900">
                                    Rp {{ number_format($selectedTotal, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-500">Ongkos Kirim</span>
                                <span class="font-medium text-stone-900">Dihitung di checkout</span>
                            </div>
                            <div class="border-t border-stone-200 pt-3">
                                <div class="flex justify-between text-base">
                                    <span class="font-semibold text-stone-900">Total</span>
                                    <span class="font-bold text-stone-900">
                                        Rp {{ number_format($selectedTotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button wire:click="proceedToCheckout"
                            {{ empty($selectedItems) || $selectedTotal == 0 ? 'disabled' : '' }}
                            class="{{ empty($selectedItems) || $selectedTotal == 0 ? 'bg-stone-200 text-stone-400 cursor-not-allowed' : 'bg-green-700 text-white hover:bg-green-800 active:scale-[0.98]' }} w-full py-3 px-4 rounded-lg font-medium text-sm transition">
                            Checkout ({{ $selectedCount }} item)
                        </button>

                        <div class="mt-4 text-center">
                            <a href="{{ route('products.index') }}"
                                class="text-green-700 hover:text-green-800 text-sm font-medium">
                                ← Lanjut Belanja
                            </a>
                        </div>

                        {{-- Promo --}}
                        <div class="mt-6 p-4 bg-green-50 rounded-xl">
                            <h4 class="font-medium text-stone-900 mb-1">Promo Hari Ini</h4>
                            <p class="text-sm text-stone-600">
                                Gratis ongkir untuk pembelian minimal Rp 100.000
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
