<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('products.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Produk</h1>
                <p class="text-gray-600">Informasi lengkap produk UMKM</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Product Images -->
            <div class="space-y-4">
                <div class="aspect-square bg-white rounded-lg overflow-hidden shadow-md">
                    <img src="{{ $productImages[$selectedImage] ?? asset('images/placeholder.png') }}"
                        alt="{{ $product->name }}" class="w-full h-full object-cover" />
                </div>
                <div class="flex gap-2 overflow-x-auto">
                    @forelse($productImages as $index => $image)
                        <button wire:click="selectImage({{ $index }})"
                            class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-colors {{ $selectedImage === $index ? 'border-green-500' : 'border-gray-200' }}">
                            <img src="{{ $image }}" alt="{{ $product->name }} {{ $index + 1 }}"
                                class="w-full h-full object-cover" />
                        </button>
                    @empty
                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">
                            Tidak ada gambar
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Product Info -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex items-center gap-1">
                            <svg class="h-5 w-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <span class="font-medium">{{ $product->rating ?? 0 }}</span>
                            <span class="text-gray-500">({{ count($mockReviews) }} ulasan)</span>
                        </div>
                        <span class="text-gray-400">•</span>
                        <span class="text-gray-600">{{ $product->sold ?? 0 }} terjual</span>
                    </div>

                    <div class="mb-6">
                        @if ($product->discount)
                            <div class="flex items-center gap-3">
                                <span class="text-3xl font-bold text-green-600">
                                    Rp
                                    {{ number_format(floor($product->price * (1 - $product->discount / 100)), 0, ',', '.') }}
                                </span>
                                <div class="flex flex-col">
                                    <span class="text-lg text-gray-400 line-through">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </span>
                                    <span class="bg-red-500 text-white px-2 py-1 rounded text-sm font-medium">
                                        -{{ $product->discount }}%
                                    </span>
                                </div>
                            </div>
                        @else
                            <span class="text-3xl font-bold text-green-600">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Store Info -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->shop->logo ?? asset('images/placeholder.png') }}"
                                alt="{{ $product->shop->name }}" class="w-12 h-12 rounded-full object-cover" />
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $product->shop->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $product->shop->address ?? 'Tidak tersedia' }}</p>
                                <div class="flex items-center gap-2 text-sm">
                                    <svg class="h-4 w-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <span>{{ $product->shop->rating ?? 0 }}</span>
                                    <span class="text-gray-400">•</span>
                                    <span class="text-gray-600">{{ $product->shop->response_rate ?? 0 }}% respon</span>
                                </div>
                            </div>
                        </div>
                        {{-- <a href="{{ route('shops.show', $product->shop->id) }}"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            Lihat Toko
                        </a> --}}
                    </div>
                </div>

                <!-- Quantity and Actions -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center border border-gray-300 rounded-lg">
                                <button wire:click="decrementQuantity" class="p-2 hover:bg-gray-100 transition-colors">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                <span class="px-4 py-2 font-medium">{{ $quantity }}</span>
                                <button wire:click="incrementQuantity"
                                    {{ $quantity >= $product->stock ? 'disabled' : '' }}
                                    class="p-2 hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                            <span class="text-sm text-gray-600">
                                Stok: {{ $product->stock }} tersisa
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="addToCart" {{ $product->stock === 0 ? 'disabled' : '' }}
                            class="{{ $product->stock === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-green-600 text-white hover:bg-green-700' }} flex-1 py-3 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.957-3.83A1 1 0 005 1H3z">
                                </path>
                            </svg>
                            {{ $product->stock === 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                        </button>

                        @if ($product->stock > 0)
                            <button wire:click="orderViaWhatsApp"
                                class="flex-1 py-3 px-4 rounded-lg font-medium bg-green-500 text-white hover:bg-green-600 transition-colors flex items-center justify-center gap-2">
                                💬 Pesan via WhatsApp
                            </button>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        <button
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                            Favorit
                        </button>
                        <button
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C9.589 12.437 10 11.174 10 9.5c0-1.933-.716-3.696-1.9-5.0m0 0C3.9 2.7 1 5.6 1 9.5s2.9 6.8 7.1 8.3m0 0c1.184-1.304 1.9-3.067 1.9-5.0c0-1.933-.716-3.696-1.9-5">
                                </path>
                            </svg>
                            Bagikan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details and Reviews -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex">
                    <button wire:click="$set('showReviews', false)"
                        class="px-6 py-4 font-medium text-sm transition-colors {{ !$showReviews ? 'bg-green-50 text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        Deskripsi Produk
                    </button>
                    <button wire:click="$set('showReviews', true)"
                        class="px-6 py-4 font-medium text-sm transition-colors {{ $showReviews ? 'bg-green-50 text-green-600 border-b-2 border-green-600' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        Ulasan ({{ count($mockReviews) }})
                    </button>
                </nav>
            </div>

            <div class="p-6">
                @if (!$showReviews)
                    <!-- Description Tab -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Deskripsi Produk</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Spesifikasi</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Kategori:</span>
                                        <span
                                            class="font-medium">{{ $product->category->name ?? 'Tidak tersedia' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Berat:</span>
                                        <span class="font-medium">{{ $product->weight_gram }} gram</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Kondisi:</span>
                                        <span class="font-medium">Baru</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Asal:</span>
                                        <span
                                            class="font-medium">{{ $product->shop->address ?? 'Tidak tersedia' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Informasi Pengiriman</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Pengiriman dari:</span>
                                        <span
                                            class="font-medium">{{ $product->shop->address ?? 'Tidak tersedia' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Ongkir:</span>
                                        <span class="font-medium">Mulai Rp 15.000</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Estimasi tiba:</span>
                                        <span class="font-medium">1-3 hari kerja</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Reviews Tab -->
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Ulasan Pembeli</h3>
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                                <span class="font-medium">{{ number_format($averageRating, 1) }}</span>
                                <span class="text-gray-500">dari {{ count($mockReviews) }} ulasan</span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach ($mockReviews as $review)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="h-5 w-5 text-green-600" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <div>
                                                    <h4 class="font-medium text-gray-900">{{ $review['user'] }}</h4>
                                                    <div class="flex items-center gap-1">
                                                        @for ($i = 0; $i < $review['rating']; $i++)
                                                            <svg class="h-4 w-4 text-yellow-400 fill-current"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                                </path>
                                                            </svg>
                                                        @endfor
                                                        @for ($i = $review['rating']; $i < 5; $i++)
                                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                                </path>
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <span class="text-sm text-gray-500">
                                                    {{ \Carbon\Carbon::parse($review['date'])->translatedFormat('d F Y') }}
                                                </span>
                                            </div>
                                            <p class="text-gray-700 mb-3">{{ $review['comment'] }}</p>
                                            <div class="flex items-center gap-4 text-sm">
                                                <button
                                                    class="flex items-center gap-1 text-gray-600 hover:text-green-600 transition-colors">
                                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M2 10.5a1.5 1.5 0 113 0v-6a1.5 1.5 0 01-3 0v6zM14 4a1 1 0 100 2h1a1 1 0 100-2h-1zm-2 7a4 4 0 118 0 4 4 0 01-8 0zm12-3h-1.535A5.99 5.99 0 0014 6.5V4a1 1 0 00-1-1H9.5a1 1 0 00-1 1v1H5V4a1 1 0 00-1-1H1.5a1 1 0 00-1 1v2a1 1 0 001 1v10a2 2 0 002 2h10a2 2 0 002-2v-10a1 1 0 001-1V4a1 1 0 00-1-1z">
                                                        </path>
                                                    </svg>
                                                    Membantu ({{ $review['helpful'] }})
                                                </button>
                                                <button
                                                    class="flex items-center gap-1 text-gray-600 hover:text-red-600 transition-colors">
                                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 4a1 1 0 100 2h1a1 1 0 100-2h-1zm2 7a4 4 0 11-8 0 4 4 0 018 0zm-2-3h-1.535A5.99 5.99 0 0016 6.5V4a1 1 0 011-1h4.5a1 1 0 011 1v2a1 1 0 01-1 1v10a2 2 0 01-2 2h-10a2 2 0 01-2-2v-10a1 1 0 01-1-1V4a1 1 0 011-1h1z">
                                                        </path>
                                                    </svg>
                                                    Tidak membantu
                                                </button>
                                                <button
                                                    class="flex items-center gap-1 text-gray-600 hover:text-blue-600 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                                        </path>
                                                    </svg>
                                                    Balas
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center">
                            <button
                                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                Muat Lebih Banyak Ulasan
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
