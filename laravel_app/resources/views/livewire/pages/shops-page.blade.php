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
    $wireTargets = 'search,sortBy,location,category,minRating,toggleFilters,clearFilters,updateViewMode';
@endphp

<div class="bg-stone-50 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="pb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-green-700">Daftar UMKM</p>
            <h1 class="mt-2 text-3xl md:text-4xl font-bold tracking-tight text-stone-900">UMKM Cilacap</h1>
            <p class="text-stone-500 mt-2">
                @if ($search)
                    Hasil pencarian "{{ $search }}"
                @else
                    Temukan {{ $filteredShops->total() }}+ UMKM terpercaya di Cilacap
                @endif
            </p>
        </div>

        {{-- Search --}}
        <div class="mb-6 relative max-w-md">
            <svg class="h-4 w-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Cari UMKM, lokasi, atau kategori..."
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

        {{-- Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-3">
                {{-- Toggle filter: mobile saja (sidebar selalu tampil di lg) --}}
                <button wire:click="toggleFilters"
                    class="lg:hidden flex items-center gap-2 px-4 py-2 bg-white border border-stone-200 rounded-lg hover:border-green-700 transition-colors text-sm font-medium text-stone-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.587a2.25 2.25 0 00-.659-1.591L3.659 7.39A2.25 2.25 0 013 5.799V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Filter
                    @if ($activeFilters > 0)
                        <span class="bg-green-700 text-white text-[10px] font-semibold rounded-full px-1.5 py-0.5">{{ $activeFilters }}</span>
                    @endif
                </button>

                {{-- View mode segmented control --}}
                <div class="inline-flex rounded-lg bg-stone-100 p-1" role="group" aria-label="Mode tampilan">
                    <button wire:click="updateViewMode('grid')" aria-pressed="{{ $viewMode === 'grid' ? 'true' : 'false' }}"
                        class="rounded-md px-2.5 py-1.5 transition {{ $viewMode === 'grid' ? 'bg-white text-green-700 shadow-[0_1px_2px_rgba(28,25,23,0.08)]' : 'text-stone-500 hover:text-stone-700' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </button>
                    <button wire:click="updateViewMode('list')" aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}"
                        class="rounded-md px-2.5 py-1.5 transition {{ $viewMode === 'list' ? 'bg-white text-green-700 shadow-[0_1px_2px_rgba(28,25,23,0.08)]' : 'text-stone-500 hover:text-stone-700' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-stone-500 hidden sm:inline">
                    {{ $filteredShops->total() }} UMKM
                </span>
                <x-select-dropdown prop="sortBy" label="Urutkan" class="w-56"
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
                    @include('livewire.pages.partials.shop-filters')
                </div>
            </aside>

            {{-- Results --}}
            <div class="flex-1 min-w-0">
                <div wire:loading wire:target="{{ $wireTargets }}" class="flex items-center gap-2 text-sm text-stone-500 mb-3">
                    <span class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-stone-300 border-t-green-700"></span>
                    Memuat ulang…
                </div>

                <div wire:loading.class="opacity-50 transition-opacity duration-200" wire:target="{{ $wireTargets }}">
                    @if ($viewMode === 'grid')
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" wire:key="shops-grid">
                            @forelse($filteredShops as $shop)
                                <div wire:key="shop-{{ $shop->id }}" class="reveal group overflow-hidden rounded-xl border border-stone-200/70 bg-white shadow-[0_1px_2px_rgba(28,25,23,0.05)] transition hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]" style="--i: {{ $loop->index % 8 }}">
                                    @include('components.shop-card-grid', ['shop' => $shop])
                                </div>
                            @empty
                                @include('livewire.pages.partials.shops-empty')
                            @endforelse
                        </div>
                    @else
                        <div class="space-y-4" wire:key="shops-list">
                            @forelse($filteredShops as $shop)
                                <div wire:key="shop-{{ $shop->id }}" class="reveal group flex gap-5 p-4 sm:p-5 overflow-hidden rounded-xl border border-stone-200/70 bg-white shadow-[0_1px_2px_rgba(28,25,23,0.05)] transition hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]" style="--i: {{ $loop->index % 8 }}">
                                    @include('components.shop-card-list', ['shop' => $shop])
                                </div>
                            @empty
                                @include('livewire.pages.partials.shops-empty')
                            @endforelse
                        </div>
                    @endif
                </div>

                @if ($filteredShops->hasPages())
                    <div class="mt-8">
                        {{ $filteredShops->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom sheet mobile --}}
    @if ($showFilters)
        <div class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-stone-900/40" wire:click="toggleFilters"></div>
            <div class="absolute inset-x-0 bottom-0 max-h-[80vh] overflow-y-auto bg-white rounded-t-2xl p-5 pb-8 reveal">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-stone-900">Filter</h3>
                    <button wire:click="toggleFilters" class="p-2 text-stone-400 hover:text-stone-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @include('livewire.pages.partials.shop-filters')
                <div class="sticky bottom-0 -mx-5 px-5 pt-4 pb-1 bg-white">
                    <button wire:click="toggleFilters"
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
