@php
    $input = 'w-full rounded-lg border border-stone-300 px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700';
    $iconBox = 'bg-green-50 text-green-700 rounded-lg p-2.5 h-fit shrink-0';
@endphp

<div class="bg-stone-50 py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8">
            <button wire:click="goBack" class="p-2 hover:bg-stone-100 rounded-lg transition-colors">
                <svg class="h-5 w-5 text-stone-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Checkout</p>
                <h1 class="mt-1 text-2xl md:text-3xl font-bold tracking-tight text-stone-900">Selesaikan pesanan Anda</h1>
            </div>
        </div>

        {{-- Progress steps --}}
        <div class="mb-10 overflow-x-auto">
            <div class="flex items-center min-w-max">
                @php
                    $steps = [
                        ['key' => 'address', 'label' => 'Alamat'],
                        ['key' => 'payment', 'label' => 'Pembayaran'],
                        ['key' => 'review', 'label' => 'Review'],
                        ['key' => 'success', 'label' => 'Selesai'],
                    ];
                    $stepOrder = ['address', 'payment', 'review', 'success'];
                    $currentIndex = array_search($step, $stepOrder);
                @endphp

                @foreach ($steps as $index => $stepItem)
                    @php
                        $isActive = $step === $stepItem['key'];
                        $isCompleted = $currentIndex > $index;
                    @endphp

                    <div class="flex items-center">
                        <span
                            class="flex items-center justify-center h-8 w-8 rounded-full text-sm font-semibold {{ $isActive ? 'bg-green-700 text-white' : ($isCompleted ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-stone-100 text-stone-400') }}">
                            @if ($isCompleted)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        <span class="ml-2 text-sm font-medium {{ $isActive || $isCompleted ? 'text-stone-900' : 'text-stone-400' }}">
                            {{ $stepItem['label'] }}
                        </span>
                        @if ($index < 3)
                            <div class="w-12 md:w-20 h-px mx-4 {{ $isCompleted ? 'bg-green-700' : 'bg-stone-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main content --}}
            <div class="lg:col-span-2">
                {{-- Step: Alamat --}}
                @if ($step === 'address')
                    <div class="rounded-xl border border-stone-200/70 bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                        <h2 class="text-lg font-semibold text-stone-900 mb-6">Alamat Pengiriman</h2>

                        <form wire:submit="submitAddress" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Nama Penerima *</label>
                                    <input type="text" wire:model="name" required
                                        class="{{ $input }}" placeholder="Nama lengkap penerima" />
                                    @error('name')
                                        <span class="text-sm text-red-600 mt-1.5 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Nomor Telepon *</label>
                                    <input type="tel" wire:model="phone" required
                                        class="{{ $input }}" placeholder="08xxxxxxxxxx" />
                                    @error('phone')
                                        <span class="text-sm text-red-600 mt-1.5 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-stone-700 mb-1.5">Alamat Lengkap *</label>
                                <textarea wire:model="address" required rows="3" class="{{ $input }}"
                                    placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan"></textarea>
                                @error('address')
                                    <span class="text-sm text-red-600 mt-1.5 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Kota</label>
                                    <input type="text" wire:model="city" class="{{ $input }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-stone-700 mb-1.5">Kode Pos</label>
                                    <input type="text" wire:model="postalCode" class="{{ $input }}" placeholder="53xxx" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-stone-700 mb-1.5">Catatan untuk Kurir (Opsional)</label>
                                <textarea wire:model="notes" rows="2" class="{{ $input }}"
                                    placeholder="Patokan atau instruksi khusus"></textarea>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Metode Pengiriman</h3>
                                <div class="space-y-3">
                                    @foreach ($shippingOptions as $option)
                                        <label @class([
                                            'flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition',
                                            'border-green-700 bg-green-50/50' => $shippingMethod === $option['id'],
                                            'border-stone-200 hover:border-stone-300' => $shippingMethod !== $option['id'],
                                        ])>
                                            <input type="radio" wire:model.live="shippingMethod" value="{{ $option['id'] }}"
                                                class="mt-1 accent-green-700" />
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-3">
                                                    <span class="{{ $iconBox }}">
                                                        @switch($option['icon'])
                                                            @case('store')
                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0V3.75C7.5 4.694 8.371 5.25 9 5.25c.63 0 1.5-.556 1.5-1.5V9.35m0 0h4.5m4.5-5.6V9.35m0 0V3.75c0 .944.871 1.5 1.5 1.5s1.5-.556 1.5-1.5v5.6m-3 0H6" />
                                                                </svg>
                                                            @break
                                                        @case('truck')
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                                            </svg>
                                                        @break
                                                    @endswitch
                                                </span>
                                                    <span class="font-medium text-stone-900 text-sm">{{ $option['name'] }}</span>
                                                </div>
                                                <p class="text-sm text-stone-500 mt-1">{{ $option['desc'] }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('shippingMethod')
                                    <span class="text-sm text-red-600 mt-1.5 block">{{ $message }}</span>
                                @enderror

                                @if (! $deliveryAvailable)
                                    <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-3 mt-3">
                                        Pengiriman ke lokasi belum tersedia
                                        {{ $checkoutShop ? 'untuk toko ini' : 'karena belanja dari lebih dari satu toko' }}.
                                        Pilih ambil di toko atau checkout per toko.
                                    </p>
                                @endif
                            </div>

                            @if ($shippingMethod === 'delivery' && $deliveryAvailable)
                                <div class="border border-stone-200 rounded-xl p-4 space-y-3">
                                    <div>
                                        <h3 class="text-sm font-medium text-stone-900">Lokasi Pengiriman</h3>
                                        <p class="text-sm text-stone-500 mt-1">
                                            Baca lokasi perangkat Anda, lalu geser pin jika titiknya kurang tepat.
                                            Ongkir dihitung dari jarak ke {{ $checkoutShop?->name ?? 'toko' }}.
                                        </p>
                                    </div>

                                    @include('livewire.pages.partials.checkout-delivery-map')

                                    @error('deliveryLatitude')
                                        <span class="text-sm text-red-600 mt-1.5 block">{{ $message }}</span>
                                    @enderror

                                    @if ($deliveryQuote)
                                        <div class="rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-800">
                                            <p>Jarak ke toko: <strong>{{ $deliveryDistanceLabel }}</strong>
                                                ({{ $deliveryQuote['units'] }} satuan)</p>
                                            <p>Ongkir:
                                                <strong>Rp {{ number_format($deliveryQuote['cost'], 0, ',', '.') }}</strong>
                                            </p>
                                        </div>
                                    @else
                                        <p class="text-sm text-stone-500">Tandai lokasi di peta untuk melihat ongkir.</p>
                                    @endif
                                </div>
                            @endif

                            <button type="submit"
                                class="w-full bg-green-700 text-white py-3 px-4 rounded-lg font-medium text-sm hover:bg-green-800 active:scale-[0.98] transition">
                                Lanjut ke Pembayaran
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Step: Pembayaran --}}
                @if ($step === 'payment')
                    <div class="rounded-xl border border-stone-200/70 bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                        <h2 class="text-lg font-semibold text-stone-900 mb-6">Metode Pembayaran</h2>

                        <form wire:submit="submitPayment">
                            <div class="space-y-3 mb-6">
                                @foreach ($paymentOptions as $option)
                                    <label @class([
                                        'flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition',
                                        'border-green-700 bg-green-50/50' => $paymentMethod === $option['id'],
                                        'border-stone-200 hover:border-stone-300' => $paymentMethod !== $option['id'],
                                    ])>
                                        <input type="radio" wire:model.live="paymentMethod" value="{{ $option['id'] }}"
                                            class="accent-green-700" />
                                        <span class="{{ $iconBox }}">
                                            @switch($option['icon'])
                                                @case('qrcode')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75h.008v.008h-.008v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-4.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM13.5 15.75h.008v.008H13.5v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm2.625 2.625h.008v.008h-.008v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-4.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm2.25-2.625a.375.375 0 11-.75 0 .375.375 0 01.75 0zM13.5 18.75h.008v.008H13.5v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                    </svg>
                                                @break
                                    @case('cash')
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                        </svg>
                                    @break
                                @endswitch
                            </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-stone-900 text-sm">{{ $option['name'] }}</div>
                                            <div class="text-sm text-stone-500 mt-0.5">{{ $option['desc'] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            @if ($paymentMethod === 'qris')
                                <p class="text-sm text-stone-600 mb-4">
                                    Setelah pesanan dibuat, Anda akan melihat QR toko. Bayar sesuai total, lalu unggah bukti.
                                    QRIS hanya untuk belanja dari satu toko yang sudah mengatur kode QRIS.
                                </p>
                            @endif

                            <button type="submit"
                                class="w-full bg-green-700 text-white py-3 px-4 rounded-lg font-medium text-sm hover:bg-green-800 active:scale-[0.98] transition">
                                Review Pesanan
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Step: Review --}}
                @if ($step === 'review')
                    <div class="rounded-xl border border-stone-200/70 bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                        <h2 class="text-lg font-semibold text-stone-900 mb-6">Review Pesanan</h2>

                        <div class="space-y-6">
                            <div>
                                <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-3">Item Pesanan</h3>
                                <div class="space-y-3">
                                    @foreach ($checkoutItems as $productId => $item)
                                        <div wire:key="review-item-{{ $productId }}"
                                            class="flex gap-4 p-4 rounded-xl bg-stone-50">
                                            <img src="{{ data_get($item, 'image') ? (filter_var(data_get($item, 'image'), FILTER_VALIDATE_URL) ? data_get($item, 'image') : Storage::url(data_get($item, 'image'))) : 'https://picsum.photos/seed/cismart-p' . $productId . '/80/80' }}" alt="{{ data_get($item, 'name', 'Produk') }}"
                                                class="w-16 h-16 object-cover rounded-lg" />
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-stone-900 text-sm">{{ $item['name'] }}</p>
                                                <p class="text-sm text-stone-500 mt-0.5">{{ $item['shop_name'] }}</p>
                                                <p class="text-sm text-stone-500 mt-2">Qty: {{ $item['quantity'] }} ×
                                                    Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-stone-900 text-sm">
                                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Alamat Pengiriman</h3>
                                    <div class="bg-stone-50 p-4 rounded-xl text-sm">
                                        <p class="font-medium text-stone-900">{{ $name }}</p>
                                        <p class="text-stone-600">{{ $phone }}</p>
                                        <p class="text-stone-600">{{ $address }}</p>
                                        <p class="text-stone-600">{{ $city }} {{ $postalCode }}</p>
                                        @if ($notes)
                                            <p class="text-stone-500 text-xs mt-1">Catatan: {{ $notes }}</p>
                                        @endif
                                        <p class="text-stone-700 text-xs mt-2">
                                            {{ collect($shippingOptions)->firstWhere('id', $shippingMethod)['name'] ?? $shippingMethod }}
                                            @if ($shippingMethod === 'delivery' && $deliveryDistanceLabel)
                                                · {{ $deliveryDistanceLabel }}
                                                · Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Metode Pembayaran</h3>
                                    <div class="bg-stone-50 p-4 rounded-xl text-sm">
                                        <p class="text-stone-700">{{ collect($paymentOptions)->firstWhere('id', $paymentMethod)['name'] ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>

                            <button wire:click="submitOrder" wire:loading.attr="disabled"
                                class="{{ $loading ? 'bg-stone-200 text-stone-400 cursor-not-allowed' : 'bg-green-700 text-white hover:bg-green-800 active:scale-[0.98]' }} w-full py-3 px-4 rounded-lg font-medium text-sm transition">
                                <span wire:loading.remove wire:target="submitOrder">Buat Pesanan</span>
                                <span wire:loading wire:target="submitOrder">Memproses Pesanan…</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Step: Selesai --}}
                @if ($step === 'success')
                    <div class="rounded-xl border border-stone-200/70 bg-white p-8 text-center shadow-[0_1px_2px_rgba(28,25,23,0.05)]">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-green-50 text-green-700 mb-4">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <h2 class="text-2xl font-bold text-stone-900 mb-3">Pesanan Berhasil Dibuat!</h2>
                        <p class="text-stone-600 mb-6 max-w-md mx-auto leading-relaxed">
                            Terima kasih telah berbelanja di CISMART.
                            Pesananmu sudah diteruskan ke penjual. Bayar langsung saat pesanan diambil
                            atau diantar, lalu tunggu konfirmasi dari penjual.
                        </p>


                        <div class="space-y-3 max-w-sm mx-auto">
                            <a href="{{ route('home') }}"
                                class="block w-full bg-green-700 text-white py-3 px-4 rounded-lg font-medium text-sm hover:bg-green-800 active:scale-[0.98] transition">
                                Ke Beranda
                            </a>
                            <a href="{{ route('products.index') }}"
                                class="block w-full border border-stone-300 text-stone-700 py-3 px-4 rounded-lg font-medium text-sm hover:border-green-700 hover:text-green-700 transition">
                                Lanjut Belanja
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Order Summary Sidebar --}}
            @if ($step !== 'success')
                <div class="lg:col-span-1">
                    <div class="rounded-xl border border-stone-200/70 bg-white p-6 shadow-[0_1px_2px_rgba(28,25,23,0.05)] sticky top-24">
                        <h3 class="text-sm font-semibold text-stone-900 mb-4">Ringkasan Pesanan</h3>

                        <div class="space-y-3 mb-4">
                            @foreach ($checkoutItems as $productId => $item)
                                <div wire:key="checkout-item-{{ $productId }}" class="flex gap-3">
                                    <img src="{{ data_get($item, 'image') ? (filter_var(data_get($item, 'image'), FILTER_VALIDATE_URL) ? data_get($item, 'image') : Storage::url(data_get($item, 'image'))) : 'https://picsum.photos/seed/cismart-p' . $productId . '/80/80' }}" alt="{{ data_get($item, 'name', 'Produk') }}"
                                        class="w-12 h-12 object-cover rounded-lg" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-stone-900 truncate">{{ $item['name'] }}</p>
                                        <p class="text-sm text-stone-500">{{ $item['shop_name'] }}</p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-stone-500">Qty: {{ $item['quantity'] }}</span>
                                            <span class="text-sm font-medium text-stone-900">
                                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-stone-200 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-stone-500">Subtotal</span>
                                <span class="font-medium text-stone-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-500">
                                    Ongkir
                                    @if ($shippingMethod === 'delivery' && $deliveryDistanceLabel)
                                        <span class="block text-xs text-stone-400">{{ $deliveryDistanceLabel }}</span>
                                    @endif
                                </span>
                                <span class="font-medium text-stone-900">
                                    @if ($shippingMethod === 'pickup')
                                        Gratis
                                    @elseif ($shippingMethod === 'delivery' && ! $deliveryQuote)
                                        —
                                    @else
                                        Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                            <div class="border-t border-stone-200 pt-2">
                                <div class="flex justify-between text-base">
                                    <span class="font-semibold text-stone-900">Total</span>
                                    <span class="font-bold text-stone-900">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-green-50 rounded-xl">
                            <div class="flex items-center gap-2 text-green-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                <span class="text-sm font-medium">Transaksi Aman</span>
                            </div>
                            <p class="text-xs text-green-700 mt-1">
                                Data Anda dilindungi dengan enkripsi SSL
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
        });

        window.checkoutDeliveryMap = function () {
            return {
                shopLat: null,
                shopLng: null,
                map: null,
                customerMarker: null,
                shopMarker: null,
                routeLine: null,
                locating: false,
                locateError: '',

                init() {
                    const lat = this.$el.dataset.shopLat;
                    const lng = this.$el.dataset.shopLng;
                    this.shopLat = lat === '' ? null : parseFloat(lat);
                    this.shopLng = lng === '' ? null : parseFloat(lng);
                    this.$nextTick(() => this.initMap());
                },

                initMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initMap(), 150);
                        return;
                    }

                    if (this.map || this.$refs.mapContainer._leaflet_id) {
                        return;
                    }

                    const defaultLat = this.shopLat ?? -7.7181;
                    const defaultLng = this.shopLng ?? 109.0181;
                    const customerLat = parseFloat(this.$wire.deliveryLatitude);
                    const customerLng = parseFloat(this.$wire.deliveryLongitude);
                    const hasCustomer = !isNaN(customerLat) && !isNaN(customerLng);

                    this.map = L.map(this.$refs.mapContainer).setView(
                        [hasCustomer ? customerLat : defaultLat, hasCustomer ? customerLng : defaultLng],
                        hasCustomer ? 15 : 14
                    );

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    if (this.shopLat !== null && this.shopLng !== null) {
                        this.shopMarker = L.circleMarker([this.shopLat, this.shopLng], {
                            radius: 10,
                            color: '#15803d',
                            fillColor: '#166534',
                            fillOpacity: 1,
                            weight: 2,
                        }).addTo(this.map).bindPopup('Lokasi toko');
                    }

                    if (hasCustomer) {
                        this.placeCustomerMarker(customerLat, customerLng, false);
                    }

                    this.map.on('click', (e) => {
                        this.placeCustomerMarker(e.latlng.lat, e.latlng.lng, true);
                    });

                    setTimeout(() => {
                        try { this.map.invalidateSize(); } catch (e) {}
                    }, 200);
                },

                placeCustomerMarker(lat, lng, persist) {
                    const position = [lat, lng];

                    if (this.customerMarker) {
                        this.customerMarker.setLatLng(position);
                    } else {
                        this.customerMarker = L.marker(position, {
                            draggable: true,
                            title: 'Geser untuk mengatur lokasi',
                        }).addTo(this.map);

                        this.customerMarker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.persistLocation(pos.lat, pos.lng);
                            this.drawRoute(pos.lat, pos.lng);
                        });
                    }

                    this.drawRoute(lat, lng);

                    if (persist) {
                        this.persistLocation(lat, lng);
                    }
                },

                drawRoute(lat, lng) {
                    if (this.shopLat === null || this.shopLng === null) {
                        return;
                    }

                    const points = [[this.shopLat, this.shopLng], [lat, lng]];

                    if (this.routeLine) {
                        this.routeLine.setLatLngs(points);
                        return;
                    }

                    this.routeLine = L.polyline(points, {
                        color: '#16a34a',
                        weight: 3,
                        dashArray: '6 6',
                        opacity: 0.8,
                    }).addTo(this.map);
                },

                persistLocation(lat, lng) {
                    this.$wire.setDeliveryLocation(lat, lng);
                },

                getCurrentLocation() {
                    this.locating = true;
                    this.locateError = '';

                    this.readDeviceLocation()
                        .then((position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.placeCustomerMarker(lat, lng, true);
                            if (this.map) {
                                this.map.setView([lat, lng], 16);
                            }
                        })
                        .catch((message) => {
                            this.locateError = message;
                        })
                        .finally(() => {
                            this.locating = false;
                        });
                },

                /**
                 * Baca lokasi perangkat dengan strategi bertingkat: coba pembacaan
                 * cepat (akurasi kasar/wifi) dulu, baru presisi tinggi. Meminta
                 * enableHighAccuracy langsung dengan timeout pendek sering gagal
                 * (timeout / POSITION_UNAVAILABLE) di perangkat tanpa GPS walau
                 * izin lokasi sudah diberikan.
                 */
                readDeviceLocation() {
                    if (!('geolocation' in navigator)) {
                        return Promise.reject('Browser ini tidak mendukung pembacaan lokasi.');
                    }

                    // Geolocation hanya tersedia pada konteks aman (https / localhost).
                    if (window.isSecureContext === false) {
                        return Promise.reject(
                            'Pembacaan lokasi butuh koneksi aman (HTTPS). Buka situs lewat https:// atau localhost, lalu coba lagi.'
                        );
                    }

                    const request = (options) => new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, options);
                    });

                    return request({ enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 })
                        .then(null, (error) => {
                            // Izin ditolak tidak akan berubah walau diulang.
                            if (error && error.code === 1) {
                                throw error;
                            }

                            return request({ enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
                        })
                        .then(null, (error) => Promise.reject(this.locationErrorMessage(error)));
                },

                locationErrorMessage(error) {
                    // 1 = PERMISSION_DENIED, 2 = POSITION_UNAVAILABLE, 3 = TIMEOUT
                    if (error && error.code === 1) {
                        return 'Akses lokasi diblokir untuk situs ini. Aktifkan izin lokasi di pengaturan situs browser, lalu muat ulang halaman.';
                    }
                    if (error && error.code === 2) {
                        return 'Lokasi perangkat tidak tersedia saat ini. Coba lagi, atau tandai titik di peta secara manual.';
                    }
                    if (error && error.code === 3) {
                        return 'Waktu pembacaan lokasi habis. Coba lagi, atau tandai titik di peta secara manual.';
                    }
                    return 'Tidak bisa membaca lokasi. Tandai titik di peta secara manual.';
                }
            };
        };
    </script>
@endpush
