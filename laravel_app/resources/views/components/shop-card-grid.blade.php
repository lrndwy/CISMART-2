@php
    $bannerUrl = $shop->banner
        ? (filter_var($shop->banner, FILTER_VALIDATE_URL)
            ? $shop->banner
            : Storage::url($shop->banner))
        : 'https://picsum.photos/seed/cismart-s' . $shop->id . '/800/300';
    $logoUrl = $shop->logo
        ? (filter_var($shop->logo, FILTER_VALIDATE_URL)
            ? $shop->logo
            : Storage::url($shop->logo))
        : 'https://picsum.photos/seed/cismart-l' . $shop->id . '/96/96';
@endphp

<img src="{{ $bannerUrl }}" alt="{{ $shop->name }}"
    class="w-full h-28 sm:h-32 object-cover transition-transform duration-500 group-hover:scale-[1.03]" />

<div class="p-4">
    <div class="flex items-start gap-3">
        <img src="{{ $logoUrl }}" alt="{{ $shop->name }}"
            class="h-12 w-12 rounded-full ring-2 ring-white object-cover -mt-10 relative shrink-0" />
        <div class="flex-1 min-w-0 pt-1">
            <h3 class="text-base font-semibold text-stone-900 group-hover:text-green-700 transition-colors line-clamp-1">
                <a href="{{ route('shops.show', $shop->slug) }}">{{ $shop->name }}</a>
            </h3>
            <div class="flex items-center gap-1 text-sm text-stone-500">
                <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="truncate">{{ $shop->address }}</span>
            </div>
        </div>
    </div>

    <p class="text-stone-600 text-sm mt-3 line-clamp-2">
        {{ $shop->description }}
    </p>

    <div class="mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 text-sm">
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
        @if ($shop->is_verified)
            <span class="bg-green-50 text-green-800 text-xs px-2 py-1 rounded-full font-medium inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
                Terverifikasi
            </span>
        @endif
        @if ($shop->is_power_seller)
            <span class="bg-amber-50 text-amber-700 text-xs px-2 py-1 rounded-full font-medium inline-flex items-center gap-1">
                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                Power Seller
            </span>
        @endif
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('shops.show', $shop->slug) }}"
            class="flex-1 sm:flex-none bg-green-700 hover:bg-green-800 active:scale-[0.98] text-white rounded-lg text-sm font-medium py-2.5 px-4 text-center transition">
            Lihat Toko
        </a>
    </div>
</div>
