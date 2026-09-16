<div class="group overflow-hidden rounded-xl border border-stone-200/70 bg-white shadow-[0_1px_2px_rgba(28,25,23,0.05)] transition-transform duration-300 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]">
    <div class="relative">
        <img src="{{ $this->bannerUrl }}" alt="{{ $shop->name }}"
            class="w-full h-32 object-cover transition-transform duration-500 group-hover:scale-[1.03]">
    </div>
    <div class="p-4 pt-0">
        <div class="flex items-start gap-3">
            <img src="{{ $this->logoUrl }}" alt="{{ $shop->name }}"
                class="h-12 w-12 rounded-full ring-2 ring-white object-cover -mt-10 relative shrink-0">
            <div class="flex-1 min-w-0 pt-1">
                <h3 class="font-semibold text-stone-900 group-hover:text-green-700 transition-colors line-clamp-1">
                    {{ $shop->name }}
                </h3>
                <div class="flex items-center gap-1 text-sm text-stone-500">
                    <flux:icon.map-pin class="h-3 w-3 shrink-0" />
                    <span class="truncate">{{ $shop->address ?? 'Cilacap' }}</span>
                </div>
            </div>
        </div>

        <p class="text-stone-600 text-sm mt-3 line-clamp-2">
            {{ $shop->description ?? 'Toko UMKM terpercaya di Cilacap' }}
        </p>

        <div class="mt-3 flex items-center justify-between text-sm">
            <div class="flex items-center gap-1.5">
                <svg class="h-4 w-4 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="font-medium text-stone-900">{{ $shop->rating ?? 0 }}</span>
                <span class="text-stone-500 text-xs">({{ $shop->total_reviews ?? 0 }} ulasan)</span>
            </div>
            <span class="text-xs text-stone-500">
                {{ $shop->products_count ?? 0 }} produk · {{ $shop->response_rate ?? 0 }}% respon
            </span>
        </div>

        <div class="mt-3 flex gap-2 flex-wrap">
            @if ($shop->status === 'approved')
                <span class="bg-green-50 text-green-800 text-xs px-2 py-1 rounded-full font-medium inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                    </svg>
                    Terverifikasi
                </span>
            @endif
        </div>

        <button wire:click="visitStore"
            class="mt-4 w-full rounded-lg bg-green-700 text-white text-sm font-medium py-2 hover:bg-green-800 active:scale-[0.98] transition">
            Kunjungi Toko
        </button>
    </div>
</div>
