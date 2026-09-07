<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <button wire:click="goBack" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
                <p class="text-gray-600">Selesaikan pesanan Anda</p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                @php
                    $steps = [
                        [
                            'key' => 'address',
                            'label' => 'Alamat',
                            'icon' =>
                                'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                        ],
                        [
                            'key' => 'payment',
                            'label' => 'Pembayaran',
                            'icon' =>
                                'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                        ],
                        [
                            'key' => 'review',
                            'label' => 'Review',
                            'icon' =>
                                'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        ],
                        [
                            'key' => 'success',
                            'label' => 'Selesai',
                            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
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
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-full {{ $isActive ? 'bg-green-600 text-white' : ($isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-400') }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $stepItem['icon'] }}"></path>
                            </svg>
                        </div>
                        <span
                            class="ml-2 text-sm font-medium {{ $isActive || $isCompleted ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $stepItem['label'] }}
                        </span>
                        @if ($index < 3)
                            <div class="w-16 h-1 mx-4 {{ $isCompleted ? 'bg-green-600' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Address Step -->
                @if ($step === 'address')
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Alamat Pengiriman</h2>

                        <form wire:submit="submitAddress" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nama Penerima *
                                    </label>
                                    <input type="text" wire:model="name" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Nama lengkap penerima" />
                                    @error('name')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nomor Telepon *
                                    </label>
                                    <input type="tel" wire:model="phone" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="08xxxxxxxxxx" />
                                    @error('phone')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Alamat Lengkap *
                                </label>
                                <textarea wire:model="address" required rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan"></textarea>
                                @error('address')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Kota
                                    </label>
                                    <input type="text" wire:model="city"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Kode Pos
                                    </label>
                                    <input type="text" wire:model="postalCode"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="53xxx" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Catatan untuk Kurir (Opsional)
                                </label>
                                <textarea wire:model="notes" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Patokan atau instruksi khusus"></textarea>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-900 mb-3">Metode Pengiriman</h3>
                                <div class="space-y-3">
                                    @foreach ($shippingOptions as $option)
                                        <label
                                            class="flex items-start p-4 border rounded-lg cursor-pointer {{ $shippingMethod === $option['id'] ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:bg-gray-50' }}">
                                            <input type="radio" wire:model.live="shippingMethod"
                                                value="{{ $option['id'] }}"
                                                class="mt-1 text-green-600 focus:ring-green-500" />
                                            <div class="ml-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xl">{{ $option['icon'] }}</span>
                                                    <span class="font-medium text-gray-900">{{ $option['name'] }}</span>
                                                </div>
                                                <p class="text-sm text-gray-600 mt-1">{{ $option['desc'] }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('shippingMethod')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror

                                @if (! $deliveryAvailable)
                                    <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mt-3">
                                        Pengiriman ke lokasi belum tersedia
                                        {{ $checkoutShop ? 'untuk toko ini' : 'karena belanja dari lebih dari satu toko' }}.
                                        Pilih ambil di toko atau checkout per toko.
                                    </p>
                                @endif
                            </div>

                            @if ($shippingMethod === 'delivery' && $deliveryAvailable)
                                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">Lokasi Pengiriman</h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Baca lokasi perangkat Anda, lalu geser pin jika titiknya kurang tepat.
                                            Ongkir dihitung dari jarak ke {{ $checkoutShop?->name ?? 'toko' }}.
                                        </p>
                                    </div>

                                    @include('livewire.pages.partials.checkout-delivery-map')

                                    @error('deliveryLatitude')
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
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
                                        <p class="text-sm text-gray-500">Tandai lokasi di peta untuk melihat ongkir.</p>
                                    @endif
                                </div>
                            @endif

                            <button type="submit"
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors">
                                Lanjut ke Pembayaran
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Payment Step -->
                @if ($step === 'payment')
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Metode Pembayaran</h2>

                            <form wire:submit="submitPayment">
                                <div class="space-y-3 mb-6">
                                    @foreach ($paymentOptions as $option)
                                        <label
                                            class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" wire:model.live="paymentMethod"
                                                value="{{ $option['id'] }}"
                                                class="text-green-600 focus:ring-green-500" />
                                            <div class="ml-3 flex-1">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-2xl">{{ $option['icon'] }}</span>
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ $option['name'] }}
                                                        </div>
                                                        <div class="text-sm text-gray-600">{{ $option['desc'] }}</div>
                                                    </div>
                                                </div>
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
                                    class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors">
                                    Review Pesanan
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Review Step -->
                @if ($step === 'review')
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Review Pesanan</h2>

                        <div class="space-y-6">
                            <!-- Order Items -->
                            <div>
                                <h3 class="font-medium text-gray-900 mb-3">Item Pesanan</h3>
                                <div class="space-y-3">
                                    @foreach ($checkoutItems as $productId => $item)
                                        <div wire:key="review-item-{{ $productId }}"
                                            class="flex gap-4 p-4 bg-gray-50 rounded-lg">
                                            <img src="{{ data_get($item, 'image') ? Storage::url(data_get($item, 'image')) : 'https://placehold.co/80x80' }}" alt="{{ data_get($item, 'name', 'Produk') }}"
                                                class="w-20 h-20 object-cover rounded-lg" />
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900">{{ $item['name'] }}</p>
                                                <p class="text-sm text-gray-600 mt-1">{{ $item['shop_name'] }}</p>
                                                <div class="flex items-center justify-between mt-2">
                                                    <span class="text-sm text-gray-600">Qty:
                                                        {{ $item['quantity'] }}</span>
                                                    <span class="font-medium text-gray-900">
                                                        Rp {{ number_format($item['price'], 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-semibold text-gray-900">
                                                    Rp
                                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Address Summary -->
                            <div>
                                <h3 class="font-medium text-gray-900 mb-2">Alamat Pengiriman</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="font-medium">{{ $name }}</p>
                                    <p class="text-gray-600">{{ $phone }}</p>
                                    <p class="text-gray-600">{{ $address }}</p>
                                    <p class="text-gray-600">{{ $city }} {{ $postalCode }}</p>
                                    @if ($notes)
                                        <p class="text-gray-600 text-sm mt-1">Catatan: {{ $notes }}</p>
                                    @endif
                                    <p class="text-gray-700 text-sm mt-2">
                                        {{ collect($shippingOptions)->firstWhere('id', $shippingMethod)['name'] ?? $shippingMethod }}
                                        @if ($shippingMethod === 'delivery' && $deliveryDistanceLabel)
                                            · {{ $deliveryDistanceLabel }}
                                            · Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <h3 class="font-medium text-gray-900 mb-2">Metode Pembayaran</h3>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p>{{ collect($paymentOptions)->firstWhere('id', $paymentMethod)['name'] ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Total Summary -->
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span class="text-gray-900">Total Pembayaran</span>
                                    <span class="text-green-600">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <button wire:click="submitOrder" wire:loading.attr="disabled"
                                class="{{ $loading ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700' }} w-full py-3 px-4 rounded-lg font-medium transition-colors">
                                <span wire:loading.remove wire:target="submitOrder">Buat Pesanan</span>
                                <span wire:loading wire:target="submitOrder">Memproses Pesanan...</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Success Step -->
                @if ($step === 'success')
                    <div class="bg-white rounded-lg shadow-md p-8 text-center">
                        <div class="text-6xl mb-4">🎉</div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            Pesanan Berhasil Dibuat!
                        </h2>
                        <p class="text-gray-600 mb-6">
                            Terima kasih telah berbelanja di CISMART.
                            @if ($paymentMethod === 'whatsapp-order')
                                Silakan lanjutkan komunikasi dengan penjual melalui WhatsApp yang sudah terbuka untuk
                                konfirmasi pesanan dan pembayaran.
                            @else
                                Pesanan Anda telah dikirim ke penjual melalui WhatsApp. Silakan tunggu konfirmasi dari
                                penjual.
                            @endif
                        </p>

                        @if ($paymentMethod === 'whatsapp-order')
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div class="flex items-center gap-2 text-green-700 mb-2 justify-center">
                                    <span>💬</span>
                                    <span class="font-medium">WhatsApp Terbuka</span>
                                </div>
                                <p class="text-sm text-green-600">
                                    Jika WhatsApp tidak terbuka otomatis, silakan salin pesan pesanan dan kirim manual
                                    ke penjual.
                                </p>
                            </div>
                        @endif

                        <div class="space-y-3">
                            <a href="{{ route('home') }}"
                                class="block w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors">
                                Ke Beranda
                            </a>
                            <a href="{{ route('products.index') }}"
                                class="block w-full border border-gray-300 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                                Lanjut Belanja
                            </a>
                            <button wire:click="openWhatsAppAgain"
                                class="w-full bg-green-500 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-600 transition-colors flex items-center justify-center gap-2">
                                💬 Buka WhatsApp Lagi
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Order Summary Sidebar -->
            @if ($step !== 'success')
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h3>

                        <div class="space-y-3 mb-4">
                            @foreach ($checkoutItems as $productId => $item)
                                <div wire:key="checkout-item-{{ $productId }}" class="flex gap-3">
                                    <img src="{{ data_get($item, 'image') ? Storage::url(data_get($item, 'image')) : 'https://placehold.co/80x80' }}" alt="{{ data_get($item, 'name', 'Produk') }}"
                                        class="w-12 h-12 object-cover rounded" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                                        <p class="text-sm text-gray-600">{{ $item['shop_name'] }}</p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600">Qty: {{ $item['quantity'] }}</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 pt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">
                                    Ongkir
                                    @if ($shippingMethod === 'delivery' && $deliveryDistanceLabel)
                                        <span class="block text-xs text-gray-400">{{ $deliveryDistanceLabel }}</span>
                                    @endif
                                </span>
                                <span class="font-medium">
                                    @if ($shippingMethod === 'pickup')
                                        Gratis
                                    @elseif ($shippingMethod === 'delivery' && ! $deliveryQuote)
                                        —
                                    @else
                                        Rp {{ number_format($shippingCost, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                            <div class="border-t border-gray-200 pt-2">
                                <div class="flex justify-between text-lg">
                                    <span class="font-semibold text-gray-900">Total</span>
                                    <span class="font-bold text-green-600">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Security Info -->
                        <div class="mt-6 p-4 bg-green-50 rounded-lg">
                            <div class="flex items-center gap-2 text-green-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                <span class="text-sm font-medium">Transaksi Aman</span>
                            </div>
                            <p class="text-xs text-green-600 mt-1">
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
            Livewire.on('open-whatsapp', (event) => {
                window.open(event.url, '_blank');
            });
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
                            color: '#1d4ed8',
                            fillColor: '#2563eb',
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
                    if (!navigator.geolocation) {
                        this.locateError = 'Browser tidak mendukung lokasi.';
                        return;
                    }

                    this.locating = true;
                    this.locateError = '';

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.placeCustomerMarker(lat, lng, true);
                            if (this.map) {
                                this.map.setView([lat, lng], 16);
                            }
                            this.locating = false;
                        },
                        () => {
                            this.locateError = 'Tidak bisa membaca lokasi. Izinkan akses lokasi, atau klik peta untuk menandai titik.';
                            this.locating = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                }
            };
        };
    </script>
@endpush
