<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (empty($cart))
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <svg class="h-24 w-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    Keranjang Belanja Kosong
                </h2>
                <p class="text-gray-600 mb-8">
                    Belum ada produk yang ditambahkan ke keranjang belanja Anda
                </p>
                <a href="{{ route('products.index') }}"
                    class="bg-green-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-green-700 transition-colors inline-flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Mulai Belanja
                </a>
            </div>
        @else
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('products.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Keranjang Belanja</h1>
                    <p class="text-gray-600">{{ $totalItems }} produk dalam keranjang</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md">
                        <!-- Select All Header -->
                        <div class="p-4 border-b border-gray-200">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="selectAll" wire:change="toggleSelectAll"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                <span class="ml-3 text-sm font-medium text-gray-900">
                                    Pilih Semua ({{ count($cart) }} produk)
                                </span>
                            </label>
                        </div>

                        <!-- Cart Items List -->
                        <div class="divide-y divide-gray-200">
                            @foreach ($cart as $productId => $item)
                                <div wire:key="cart-item-{{ $productId }}" class="p-4 flex gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model.live="selectedItems"
                                            value="{{ $productId }}"
                                            wire:change="toggleSelectItem({{ $productId }})"
                                            class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                    </div>

                                    <img src="{{ data_get($item, 'image', 'https://placehold.co/600x400') }}"
                                        alt="{{ data_get($item, 'name', 'Produk') }}"
                                        class="w-20 h-20 object-cover rounded-lg" />


                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate">
                                            {{ data_get($item, 'name', 'Produk') }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-2">
                                            {{ data_get($item, 'shop_name', '-') }}
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <button
                                                    wire:click="updateQuantity({{ $productId }}, {{ max(1, $item['quantity'] - 1) }})"
                                                    {{ $item['quantity'] <= 1 ? 'disabled' : '' }}
                                                    class="p-1 hover:bg-gray-100 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                    <svg class="h-4 w-4 text-gray-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                                <span class="w-12 text-center font-medium">
                                                    {{ $item['quantity'] }}
                                                </span>
                                                <button
                                                    wire:click="updateQuantity({{ $productId }}, {{ $item['quantity'] + 1 }})"
                                                    class="p-1 hover:bg-gray-100 rounded transition-colors">
                                                    <svg class="h-4 w-4 text-gray-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <div class="text-right">
                                                <div class="text-lg font-bold text-green-600">
                                                    Rp
                                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Rp {{ number_format($item['price'], 0, ',', '.') }} per item
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button wire:click="removeFromCart({{ $productId }})"
                                        wire:confirm="Hapus produk dari keranjang?"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
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

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Ringkasan Pesanan
                        </h3>

                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Produk terpilih</span>
                                <span class="font-medium">{{ $selectedCount }} item</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">
                                    Rp {{ number_format($selectedTotal, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ongkos Kirim</span>
                                <span class="font-medium text-green-600">Gratis</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg">
                                    <span class="font-semibold text-gray-900">Total</span>
                                    <span class="font-bold text-green-600">
                                        Rp {{ number_format($selectedTotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button wire:click="proceedToCheckout"
                            {{ empty($selectedItems) || $selectedTotal == 0 ? 'disabled' : '' }}
                            class="{{ empty($selectedItems) || $selectedTotal == 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700 transform hover:scale-105' }} w-full py-3 px-4 rounded-lg font-medium transition-all">
                            Checkout ({{ $selectedCount }} item)
                        </button>

                        <div class="mt-4 text-center">
                            <a href="{{ route('products.index') }}"
                                class="text-green-600 hover:text-green-700 text-sm font-medium">
                                ← Lanjut Belanja
                            </a>
                        </div>

                        <!-- Promo Section -->
                        <div class="mt-6 p-4 bg-green-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">🎉 Promo Hari Ini</h4>
                            <p class="text-sm text-gray-600">
                                Gratis ongkir untuk pembelian minimal Rp 100.000
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
