<div class="py-16 text-center @if (($viewMode ?? 'grid') === 'grid') col-span-full @endif">
    <svg class="h-12 w-12 text-stone-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.35m0 0V3.75M3.75 9.35h14.25M3.75 9.35V3.75m0 0h14.25m0 0V9.35" />
    </svg>
    <h3 class="text-lg font-semibold text-stone-900">UMKM tidak ditemukan</h3>
    <p class="text-stone-500 text-sm mt-1 mb-5">Coba ubah filter pencarian atau kata kunci yang berbeda</p>
    @if ($activeFilters > 0)
        <button wire:click="clearFilters"
            class="rounded-lg bg-green-700 text-white text-sm font-medium px-5 py-2.5 hover:bg-green-800 active:scale-[0.98] transition">
            Reset Semua Filter
        </button>
    @endif
</div>
