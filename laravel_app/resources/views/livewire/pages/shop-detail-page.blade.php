<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <a href="{{ route('shops.index') }}" class="flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                    </path>
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Store Banner -->
    <div class="relative">
        <div class="h-64 bg-cover bg-center" style="background-image: url('{{ $bannerUrl }}')">
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative -mt-16 bg-white rounded-lg shadow-lg p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex gap-4 flex-1">
                        <img src="{{ $logoUrl }}" alt="{{ $shop->name }}"
                            class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover" />
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $shop->name }}</h1>
                                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span>{{ $shop->address }}</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-1">
                                            <svg class="h-4 w-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                </path>
                                            </svg>
                                            <span class="font-medium">{{ $shop->rating ?? 0 }}</span>
                                            <span class="text-gray-500">({{ $shop->total_reviews ?? 0 }}
                                                ulasan)</span>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $shop->products_count ?? 0 }} produk
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button
                                        class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.684 13.342C9.589 12.437 10 11.174 10 9.5c0-1.933-.716-3.696-1.9-5.0m0 0C3.9 2.7 1 5.6 1 9.5s2.9 6.8 7.1 8.3m0 0c1.184-1.304 1.9-3.067 1.9-5.0c0-1.933-.716-3.696-1.9-5">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Store Badges -->
                            <div class="flex gap-2 mt-3">
                                @if ($shop->is_verified)
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                                        ✓ Terverifikasi
                                    </span>
                                @endif
                                @if ($shop->is_power_seller)
                                    <span
                                        class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-medium">
                                        ⭐ Power Seller
                                    </span>
                                @endif
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">
                                    Online
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Actions -->
                    <div class="flex flex-col gap-2 md:w-48">
                        <button wire:click="contactViaWhatsApp"
                            class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z">
                                </path>
                            </svg>
                            💬 WhatsApp Toko
                        </button>
                        <a href="tel:{{ $shop->phone }}"
                            class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            Telepon Toko
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Stats -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($achievements as $achievement)
                <div class="bg-white rounded-lg shadow-md p-4 text-center">
                    <div class="text-3xl mb-2">{{ $achievement['icon'] }}</div>
                    <div class="font-semibold text-gray-900 mb-1">{{ $achievement['label'] }}</div>
                    <div class="text-sm text-gray-600">{{ $achievement['description'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="border-b border-gray-200">
            <nav class="flex gap-8">
                <button wire:click="setActiveTab('products')"
                    class="pb-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'products' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Produk
                    <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                        {{ count($shopProducts) }}
                    </span>
                </button>
                <button wire:click="setActiveTab('about')"
                    class="pb-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'about' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Tentang Toko
                </button>
                <button wire:click="setActiveTab('reviews')"
                    class="pb-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'reviews' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Ulasan
                    <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                        {{ $shop->total_reviews ?? 0 }}
                    </span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($activeTab === 'products')
            <div>
                @if (count($shopProducts) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($shopProducts as $product)
                            <div wire:key="product-{{ $product->id }}">
                                @include('components.product-card-static', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">📦</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            Belum ada produk
                        </h3>
                        <p class="text-gray-600">
                            Toko ini sedang mempersiapkan produk-produk terbaik untuk Anda
                        </p>
                    </div>
                @endif
            </div>
        @endif

        @if ($activeTab === 'about')
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Tentang {{ $shop->name }}</h3>
                <div class="space-y-4 text-gray-600">
                    <p>{{ $shop->description }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Jam Operasional
                            </h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span>Senin - Jumat</span>
                                    <span>08:00 - 17:00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Sabtu</span>
                                    <span>08:00 - 15:00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Minggu</span>
                                    <span>Tutup</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                Kebijakan Toko
                            </h4>
                            <div class="space-y-2 text-sm">
                                <div>• Garansi kualitas produk 100%</div>
                                <div>• Retur gratis dalam 7 hari</div>
                                <div>• Pengiriman gratis minimal Rp 100.000</div>
                                <div>• Proses pesanan maksimal 1x24 jam</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($activeTab === 'reviews')
            <div class="space-y-6">
                <!-- Review Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">Ulasan Pembeli</h3>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-green-600">{{ $shop->rating ?? 0 }}</div>
                            <div class="text-sm text-gray-500">{{ $shop->total_reviews ?? 0 }} ulasan</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        <div class="md:col-span-2">
                            <div class="space-y-2">
                                @foreach ([5, 4, 3, 2, 1] as $rating)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm w-8">{{ $rating }} ⭐</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full"
                                                style="width: {{ $rating === 5 ? 70 : ($rating === 4 ? 25 : ($rating === 3 ? 5 : 0)) }}%">
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-500 w-8">
                                            {{ $rating === 5 ? 70 : ($rating === 4 ? 25 : ($rating === 3 ? 5 : 0)) }}%
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-lg font-semibold text-green-600">4.8</div>
                                    <div class="text-sm text-gray-500">Kualitas Produk</div>
                                </div>
                                <div>
                                    <div class="text-lg font-semibold text-green-600">4.7</div>
                                    <div class="text-sm text-gray-500">Kecepatan Kirim</div>
                                </div>
                                <div>
                                    <div class="text-lg font-semibold text-green-600">4.9</div>
                                    <div class="text-sm text-gray-500">Pelayanan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review List -->
                <div class="space-y-4">
                    @foreach ($mockReviews as $review)
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="font-medium text-green-600">
                                            {{ implode('', array_map(fn($n) => $n[0], explode(' ', $review['user']))) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $review['user'] }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($review['date'])->translatedFormat('d F Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex">
                                    @for ($i = 0; $i < $review['rating']; $i++)
                                        <svg class="h-4 w-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                            </path>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-gray-700 mb-3">{{ $review['comment'] }}</p>
                            <div class="text-sm text-gray-500">
                                Produk: {{ $review['product'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center">
                    <button class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        Muat Lebih Banyak Ulasan
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
