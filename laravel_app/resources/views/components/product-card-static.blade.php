<div class="group overflow-hidden rounded-xl border border-stone-200/70 bg-white shadow-[0_1px_2px_rgba(28,25,23,0.05)] transition-transform duration-300 hover:-translate-y-0.5 hover:shadow-[0_12px_28px_-14px_rgba(28,25,23,0.18)]">
    <div class="relative overflow-hidden">
        <img src="{{ $product->first_image ? (filter_var($product->first_image, FILTER_VALIDATE_URL) ? $product->first_image : Storage::url($product->first_image)) : 'https://picsum.photos/seed/cismart-p' . $product->id . '/640/480' }}"
            alt="{{ $product->name }}"
            class="aspect-[4/3] w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">

        {{-- Overlay aksi hover (desktop only; tidak berguna pada layar sentuh) --}}
        <div class="absolute top-2 right-2 hidden sm:flex gap-2 opacity-0 group-hover:opacity-100 transition">
            <a href="{{ route('products.show', $product->slug) }}" title="Lihat Detail Produk"
                class="bg-white/90 text-stone-700 hover:text-green-700 rounded-lg p-2">
                <flux:icon.eye class="h-4 w-4" />
            </a>
            <button wire:click="$dispatch('add-to-cart', { productId: {{ $product->id }} })" title="Tambah ke Keranjang"
                class="bg-white/90 text-stone-700 hover:text-green-700 rounded-lg p-2">
                <flux:icon.shopping-cart class="h-4 w-4" />
            </button>
        </div>

        <div class="absolute top-2 left-2 flex gap-1.5">
            @if ($product->is_featured)
                <span class="bg-green-700/90 text-white text-[11px] font-medium px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                    <svg class="h-3 w-3 fill-current" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    Unggulan
                </span>
            @endif
            @if ($product->stock > 0 && $product->stock < 10)
                <span class="bg-amber-500 text-white text-[11px] font-medium px-2 py-0.5 rounded-full">
                    Stok: {{ $product->stock }}
                </span>
            @elseif($product->stock === 0)
                <span class="bg-stone-500 text-white text-[11px] font-medium px-2 py-0.5 rounded-full">
                    Habis
                </span>
            @endif
        </div>
    </div>

    <div class="p-3 sm:p-4">
        <a href="{{ route('products.show', $product->slug) }}"
            class="text-[13px] sm:text-sm font-semibold leading-snug text-stone-900 line-clamp-2 group-hover:text-green-700 transition-colors block"
            title="Klik untuk melihat detail produk">
            {{ $product->name }}
        </a>

        <div class="mt-2 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[11px] sm:text-xs text-stone-500">
            <svg class="h-3.5 w-3.5 fill-amber-500 text-amber-500" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span>{{ $product->rating ?? 4.5 }}</span>
            <span class="text-stone-300">·</span>
            <span>{{ $product->sold ?? 0 }} terjual</span>
        </div>

        <p class="mt-2 text-sm sm:text-[15px] font-bold text-stone-900">
            <span class="text-xs font-medium text-stone-500">Rp</span> {{ number_format($product->price, 0, ',', '.') }}
        </p>

        <div class="mt-3 space-y-2">
            <button wire:click="$dispatch('add-to-cart', { productId: {{ $product->id }} })" @disabled($product->stock === 0)
                class="w-full rounded-lg bg-green-700 text-white text-[13px] sm:text-sm font-medium py-2.5 hover:bg-green-800 active:scale-[0.98] transition disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400">
                {{ $product->stock === 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
            </button>

        </div>
    </div>
</div>
