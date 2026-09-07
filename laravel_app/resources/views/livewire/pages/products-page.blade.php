<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Produk UMKM Cilacap</h1>
            <p class="text-gray-600">
                @if ($search)
                    Hasil pencarian "{{ $search }}"
                @else
                    Temukan produk unggulan dari UMKM lokal Cilacap
                @endif
            </p>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari produk, toko, atau kategori..."
                    class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                @if ($search)
                    <button wire:click="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                @endif
            </div>
        </div>

        <!-- Mobile Filter Section (Above Products) -->
        <div class="lg:hidden mb-6">
            <!-- Filter Toggle Button -->
            <button wire:click="toggleMobileFilter"
                class="w-full flex items-center justify-between px-4 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors mb-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    <span class="font-medium text-gray-900">Filter & Urutkan</span>
                </div>
                <svg class="h-5 w-5 transition-transform {{ $mobileFilterOpen ? 'rotate-180' : '' }}" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                    </path>
                </svg>
            </button>

            <!-- Filter Panel -->
            @if ($mobileFilterOpen)
                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <div class="space-y-4">
                        <!-- Category Filter -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2 text-sm">Kategori</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input name="category" type="radio" wire:model.live="category" value="all"
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Semua Kategori</span>
                                </label>
                                @foreach ($categories as $cat)
                                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                        <input name="category" type="radio" wire:model.live="category"
                                            value="{{ $cat->id }}" class="text-green-600 focus:ring-green-500" />
                                        <span class="ml-2 text-sm text-gray-700">
                                            {{ $cat->name }}
                                            <span class="text-gray-500 text-xs">({{ $cat->products_count }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-medium text-gray-900 mb-2 text-sm">Urutkan</h4>
                            <select wire:model.live="sortBy"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                                @foreach ($sortOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-medium text-gray-900 mb-2 text-sm">Rentang Harga</h4>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('', '')"
                                        {{ $minPrice === '' && $maxPrice === '' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Semua Harga</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('', '50000')"
                                        {{ $minPrice === '' && $maxPrice === '50000' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Di bawah Rp 50.000</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('50000', '100000')"
                                        {{ $minPrice === '50000' && $maxPrice === '100000' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Rp 50.000 - Rp 100.000</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('100000', '500000')"
                                        {{ $minPrice === '100000' && $maxPrice === '500000' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Rp 100.000 - Rp 500.000</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('500000', '')"
                                        {{ $minPrice === '500000' && $maxPrice === '' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Di atas Rp 500.000</span>
                                </label>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-medium text-gray-900 mb-2 text-sm">Lokasi UMKM</h4>
                            <select wire:model.live="location"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                                <option value="all">Semua Lokasi</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rating Filter -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-medium text-gray-900 mb-2 text-sm">Rating Minimum</h4>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:model.live="minRating" value=""
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Semua Rating</span>
                                </label>
                                @foreach ([4.5, 4.0, 3.5, 3.0] as $rating)
                                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                        <input type="radio" wire:model.live="minRating"
                                            value="{{ $rating }}"
                                            class="text-green-600 focus:ring-green-500" />
                                        <div class="ml-2 flex items-center">
                                            <span class="text-sm text-gray-700 mr-1">{{ $rating }}</span>
                                            <div class="flex">
                                                @for ($i = 0; $i < 5; $i++)
                                                    @if ($i < $rating)
                                                        <span class="text-xs text-yellow-400">⭐</span>
                                                    @else
                                                        <span class="text-xs text-gray-300">⭐</span>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="text-sm text-gray-500 ml-1">ke atas</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Active Filters & Reset -->
                        @php
                            $activeFilters = 0;
                            if ($category !== 'all') {
                                $activeFilters++;
                            }
                            if ($minPrice || $maxPrice) {
                                $activeFilters++;
                            }
                            if ($location !== 'all') {
                                $activeFilters++;
                            }
                            if ($minRating) {
                                $activeFilters++;
                            }
                        @endphp

                        @if ($activeFilters > 0)
                            <div class="border-t border-gray-200 pt-4">
                                <button wire:click="clearFilters"
                                    class="w-full px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-medium text-sm transition-colors">
                                    Reset {{ $activeFilters }} Filter
                                </button>
                            </div>
                        @endif

                        <!-- Close Button -->
                        <div class="border-t border-gray-200 pt-4">
                            <button wire:click="closeMobileFilter"
                                class="w-full px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg font-medium text-sm transition-colors">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Bar -->
            <div class="flex items-center justify-between text-sm text-gray-600 px-2">
                <span>Menampilkan {{ $filteredProducts->count() }} dari {{ $filteredProducts->total() }} produk</span>
            </div>
        </div>

        <div class="flex gap-8">
            <!-- Desktop Sidebar Filters -->
            <div class="hidden lg:block w-80 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Filter Produk</h3>
                        @php
                            $activeFilters = 0;
                            if ($category !== 'all') {
                                $activeFilters++;
                            }
                            if ($minPrice || $maxPrice) {
                                $activeFilters++;
                            }
                            if ($location !== 'all') {
                                $activeFilters++;
                            }
                            if ($minRating) {
                                $activeFilters++;
                            }
                        @endphp
                        @if ($activeFilters > 0)
                            <button wire:click="clearFilters"
                                class="text-sm text-red-600 hover:text-red-700 flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset
                            </button>
                        @endif
                    </div>

                    <div class="space-y-6">
                        <!-- Category Filter -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Kategori</h4>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input name="category" type="radio" wire:model.live="category" value="all"
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Semua Kategori</span>
                                </label>
                                @foreach ($categories as $cat)
                                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                        <input name="category" type="radio" wire:model.live="category"
                                            value="{{ $cat->id }}"
                                            class="text-green-600 focus:ring-green-500" />
                                        <span class="ml-2 text-sm text-gray-700">
                                            {{ $cat->name }}
                                            <span class="text-gray-500">({{ $cat->products_count }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="font-medium text-gray-900 mb-3">Rentang Harga</h4>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('', '')"
                                        {{ $minPrice === '' && $maxPrice === '' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Semua Harga</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('', '50000')"
                                        {{ $minPrice === '' && $maxPrice === '50000' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Di bawah Rp 50.000</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('50000', '100000')"
                                        {{ $minPrice === '50000' && $maxPrice === '100000' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Rp 50.000 - Rp 100.000</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('100000', '500000')"
                                        {{ $minPrice === '100000' && $maxPrice === '500000' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Rp 100.000 - Rp 500.000</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:click="setPriceRange('500000', '')"
                                        {{ $minPrice === '500000' && $maxPrice === '' ? 'checked' : '' }}
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Di atas Rp 500.000</span>
                                </label>
                            </div>

                            <!-- Custom Price Range -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <h5 class="text-sm font-medium text-gray-900 mb-2">Harga Kustom</h5>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" wire:model.live.debounce.500ms="minPrice" placeholder="Min"
                                        class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                                    <input type="number" wire:model.live.debounce.500ms="maxPrice" placeholder="Max"
                                        class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                                </div>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="font-medium text-gray-900 mb-3">Lokasi UMKM</h4>
                            <select wire:model.live="location"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                                <option value="all">Semua Lokasi</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rating Filter -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="font-medium text-gray-900 mb-3">Rating Minimum</h4>
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                    <input type="radio" wire:model.live="minRating" value=""
                                        class="text-green-600 focus:ring-green-500" />
                                    <span class="ml-2 text-sm text-gray-700">Semua Rating</span>
                                </label>
                                @foreach ([4.5, 4.0, 3.5, 3.0] as $rating)
                                    <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                        <input type="radio" wire:model.live="minRating"
                                            value="{{ $rating }}"
                                            class="text-green-600 focus:ring-green-500" />
                                        <div class="ml-2 flex items-center">
                                            <span class="text-sm text-gray-700 mr-1">{{ $rating }}</span>
                                            <div class="flex">
                                                @for ($i = 0; $i < 5; $i++)
                                                    @if ($i < $rating)
                                                        <span class="text-xs text-yellow-400">⭐</span>
                                                    @else
                                                        <span class="text-xs text-gray-300">⭐</span>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="text-sm text-gray-500 ml-1">ke atas</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Container -->
            <div class="flex-1">
                <!-- Desktop Top Controls -->
                <div class="hidden lg:flex items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-4">
                        @php
                            $activeFilters = 0;
                            if ($category !== 'all') {
                                $activeFilters++;
                            }
                            if ($minPrice || $maxPrice) {
                                $activeFilters++;
                            }
                            if ($location !== 'all') {
                                $activeFilters++;
                            }
                            if ($minRating) {
                                $activeFilters++;
                            }
                        @endphp

                        @if ($activeFilters > 0)
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">{{ $activeFilters }} filter aktif</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">
                            Menampilkan {{ $filteredProducts->count() }} dari {{ $filteredProducts->total() }} produk
                        </span>
                        <select wire:model.live="sortBy"
                            class="px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                            @foreach ($sortOptions as $option)
                                <option value="{{ $option['value'] }}">
                                    Urutkan: {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6" wire:key="products-grid">
                    @forelse($filteredProducts as $index => $product)
                        <div wire:key="product-{{ $product->id }}-{{ $index }}">
                            @include('components.product-card-static', ['product' => $product])
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">🔍</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                Produk tidak ditemukan
                            </h3>
                            <p class="text-gray-600 mb-4">
                                Coba ubah filter pencarian atau kata kunci yang berbeda
                            </p>
                            @php
                                $activeFilters = 0;
                                if ($category !== 'all') {
                                    $activeFilters++;
                                }
                                if ($minPrice || $maxPrice) {
                                    $activeFilters++;
                                }
                                if ($location !== 'all') {
                                    $activeFilters++;
                                }
                                if ($minRating) {
                                    $activeFilters++;
                                }
                            @endphp
                            @if ($activeFilters > 0)
                                <button wire:click="clearFilters"
                                    class="text-green-600 hover:text-green-700 font-medium">
                                    Reset Semua Filter
                                </button>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if ($filteredProducts->hasPages())
                    <div class="mt-8">
                        {{ $filteredProducts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
