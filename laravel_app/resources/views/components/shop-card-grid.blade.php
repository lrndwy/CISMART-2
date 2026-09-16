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
        <a href="https://wa.me/{{ $shop->phone ?? '6288225292279' }}?text={{ urlencode('Halo ' . $shop->name . ', saya tertarik dengan produk-produk di toko Anda.') }}"
            target="_blank" rel="noopener noreferrer" title="Hubungi via WhatsApp" aria-label="Hubungi via WhatsApp"
            class="border border-green-700 text-green-700 hover:bg-green-50 rounded-lg p-2.5 transition inline-flex items-center">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
        </a>
    </div>
</div>
