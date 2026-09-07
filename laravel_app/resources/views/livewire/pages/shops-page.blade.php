<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">UMKM Cilacap</h1>
            <p class="text-gray-600">
                @if ($search)
                    Hasil pencarian "{{ $search }}"
                @else
                    Temukan {{ $filteredShops->total() }}+ UMKM terpercaya di Cilacap
                @endif
            </p>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari UMKM, lokasi, atau kategori..."
                    class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" />
                @if ($search)
                    <button wire:click="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                @endif
            </div>
        </div>

        <!-- Controls -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <div class="flex items-center gap-4">
                <button wire:click="toggleFilters"
                    class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                        </path>
                    </svg>
                    Filter
                </button>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Tampilan:</span>
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                        <button wire:click="updateViewMode('grid')"
                            class="p-2 {{ $viewMode === 'grid' ? 'bg-green-600 text-white' : 'bg-white text-gray-600' }} transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                                </path>
                            </svg>
                        </button>
                        <button wire:click="updateViewMode('list')"
                            class="p-2 {{ $viewMode === 'list' ? 'bg-green-600 text-white' : 'bg-white text-gray-600' }} transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">
                    Menampilkan {{ $filteredShops->count() }} UMKM
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

        <div class="flex gap-8">
            <!-- Sidebar Filters -->
            @if ($showFilters)
                <div class="w-80 flex-shrink-0">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Filter UMKM</h3>
                            @php
                                $activeFilters = 0;
                                if ($location !== 'all') {
                                    $activeFilters++;
                                }
                                if ($category !== 'all') {
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
                            <!-- Location Filter -->
                            <div>
                                <h4 class="font-medium text-gray-900 mb-3">Lokasi</h4>
                                <select wire:model.live="location"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                                    <option value="all">Semua Lokasi</option>
                                    @foreach ($locations as $loc)
                                        <option value="{{ $loc }}">{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Category Filter -->
                            <div class="border-t border-gray-200 pt-6">
                                <h4 class="font-medium text-gray-900 mb-3">Kategori</h4>
                                <div class="space-y-2">
                                    @foreach ($categories as $cat)
                                        <label class="flex items-center cursor-pointer hover:bg-gray-50 p-2 rounded">
                                            <input type="radio" name="category" wire:model.live="category"
                                                value="{{ $cat['id'] }}"
                                                class="text-green-600 focus:ring-green-500" />
                                            <span class="ml-2 text-sm text-gray-700">
                                                {{ $cat['name'] }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
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
            @endif

            <!-- UMKM Grid/List -->
            <div class="flex-1">
                @if ($viewMode === 'grid')
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($filteredShops as $shop)
                            <div wire:key="shop-{{ $shop->id }}"
                                class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                                @include('components.shop-card-grid', ['shop' => $shop])
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="text-gray-400 text-6xl mb-4">🏪</div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    UMKM tidak ditemukan
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    Coba ubah filter pencarian atau kata kunci yang berbeda
                                </p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="space-y-4">
                        @forelse($filteredShops as $shop)
                            <div wire:key="shop-{{ $shop->id }}"
                                class="bg-white rounded-lg shadow-md p-4 flex gap-4 hover:shadow-lg transition-shadow">
                                @include('components.shop-card-list', ['shop' => $shop])
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="text-gray-400 text-6xl mb-4">🏪</div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                    UMKM tidak ditemukan
                                </h3>
                                <p class="text-gray-600">
                                    Coba ubah filter pencarian atau kata kunci yang berbeda
                                </p>
                            </div>
                        @endforelse
                    </div>
                @endif

                <!-- Pagination -->
                @if ($filteredShops->hasPages())
                    <div class="mt-8">
                        {{ $filteredShops->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
