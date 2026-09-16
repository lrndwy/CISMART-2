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
    $wireTargets = 'search,category,sortBy,minPrice,maxPrice,location,minRating,setPriceRange,clearFilters';
@endphp

<div class="bg-stone-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="pb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Katalog</p>
            <h1 class="mt-2 text-3xl md:text-4xl font-bold tracking-tight text-stone-900">Produk UMKM Cilacap</h1>
            <p class="text-stone-500 mt-2">
                @if ($search)
                    Hasil pencarian "{{ $search }}"
                @else
                    Temukan produk unggulan dari UMKM lokal Cilacap
                @endif
            </p>
        </div>

        {{-- Search + sort (desktop) --}}
        <div class="hidden lg:flex items-center gap-4 mb-8">
            <div class="relative flex-1 max-w-md">
                <svg class="h-4 w-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari produk, toko, atau kategori..."
                    class="w-full rounded-lg border border-stone-300 pl-10 pr-10 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700" />
                @if ($search)
                    <button wire:click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
            <x-select-dropdown prop="sortBy" label="Urutkan" class="w-56 shrink-0"
                :options="collect($sortOptions)->map(fn ($o) => ['value' => $o['value'], 'label' => $o['label']])->all()" />
        </div>

        {{-- Mobile controls --}}
        <div class="lg:hidden mb-6 space-y-4">
            <div class="relative">
                <svg class="h-4 w-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari produk, toko, atau kategori..."
                    class="w-full rounded-lg border border-stone-300 pl-10 pr-10 py-2.5 text-stone-900 placeholder:text-stone-400 focus:outline-none focus:border-transparent focus:ring-2 focus:ring-green-700" />
                @if ($search)
                    <button wire:click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-3">
            <button wire:click="toggleMobileFilter"
                class="w-full flex items-center justify-between px-4 py-3 bg-white border border-stone-200 rounded-lg hover:border-green-700 transition-colors">
                <span class="flex items-center gap-2 font-medium text-stone-900 text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.587a2.25 2.25 0 00-.659-1.591L3.659 7.39A2.25 2.25 0 013 5.799V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Filter
                    @if ($activeFilters > 0)
                        <span class="bg-green-700 text-white text-[10px] font-semibold rounded-full px-1.5 py-0.5">{{ $activeFilters }}</span>
                    @endif
                </span>
                <svg class="h-4 w-4 text-stone-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <x-select-dropdown prop="sortBy" label="Urutkan"
                :options="collect($sortOptions)->map(fn ($o) => ['value' => $o['value'], 'label' => $o['label']])->all()" />
            </div>
        </div>

        <div class="flex gap-10">
            {{-- Sidebar desktop --}}
            <aside class="hidden lg:block lg:w-72 flex-shrink-0">
                <div class="sticky top-24">
                    <div class="flex items-center justify-between pb-4">
                        <h3 class="text-sm font-semibold text-stone-900">Filter</h3>
                        @if ($activeFilters > 0)
                            <button wire:click="clearFilters" class="text-sm font-medium text-red-600 hover:text-red-700">
                                Reset ({{ $activeFilters }})
                            </button>
                        @endif
                    </div>
                    @include('livewire.pages.partials.product-filters')
                </div>
            </aside>

            {{-- Results --}}
            <div class="flex-1 min-w-0">
                {{-- Toolbar --}}
                <div class="hidden lg:flex items-center justify-between text-sm text-stone-500 border-t border-stone-200 py-3 mb-5">
                    <span>Menampilkan {{ $filteredProducts->count() }} dari {{ $filteredProducts->total() }} produk</span>
                    @if ($activeFilters > 0)
                        <span><span class="text-stone-900 font-medium">{{ $activeFilters }} filter aktif</span></span>
                    @endif
                </div>

                <div wire:loading wire:target="{{ $wireTargets }}" class="flex items-center gap-2 text-sm text-stone-500 mb-3">
                    <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-stone-300 border-t-green-700"></span>
                    Memuat ulang…
                </div>

                {{-- Grid --}}
                <div class="grid grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6" wire:key="products-grid"
                    wire:loading.class="opacity-50 transition-opacity duration-200" wire:target="{{ $wireTargets }}">
                    @forelse($filteredProducts as $index => $product)
                        <div wire:key="product-{{ $product->id }}-{{ $index }}" class="reveal" style="--i: {{ $loop->index % 8 }}">
                            @include('components.product-card-static', ['product' => $product])
                        </div>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <svg class="h-12 w-12 text-stone-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-stone-900">Produk tidak ditemukan</h3>
                            <p class="text-stone-500 text-sm mt-1 mb-5">
                                Coba ubah filter pencarian atau kata kunci yang berbeda
                            </p>
                            @if ($activeFilters > 0)
                                <button wire:click="clearFilters"
                                    class="rounded-lg bg-green-700 text-white text-sm font-medium px-5 py-2.5 hover:bg-green-800 active:scale-[0.98] transition">
                                    Reset Semua Filter
                                </button>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if ($filteredProducts->hasPages())
                    <div class="mt-8">
                        {{ $filteredProducts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom sheet mobile --}}
    @if ($mobileFilterOpen)
        <div class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-stone-900/40" wire:click="closeMobileFilter"></div>
            <div class="absolute inset-x-0 bottom-0 max-h-[80vh] overflow-y-auto bg-white rounded-t-2xl p-5 pb-8 reveal">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-stone-900">Filter</h3>
                    <button wire:click="closeMobileFilter" class="p-2 text-stone-400 hover:text-stone-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @include('livewire.pages.partials.product-filters')
                <div class="sticky bottom-0 -mx-5 px-5 pt-4 pb-1 bg-white">
                    <button wire:click="closeMobileFilter"
                        class="w-full rounded-lg bg-green-700 text-white py-3 font-medium text-sm hover:bg-green-800 active:scale-[0.98] transition">
                        Terapkan
                        @if ($activeFilters > 0)
                            ({{ $activeFilters }})
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
