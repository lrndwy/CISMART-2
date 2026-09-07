@php
    $bannerUrl = $shop->banner
        ? (filter_var($shop->banner, FILTER_VALIDATE_URL)
            ? $shop->banner
            : Storage::url($shop->banner))
        : asset('images/shop-banner-placeholder.jpg');
    $logoUrl = $shop->logo
        ? (filter_var($shop->logo, FILTER_VALIDATE_URL)
            ? $shop->logo
            : Storage::url($shop->logo))
        : asset('images/shop-logo-placeholder.jpg');
@endphp

<a href="{{ route('shops.show', $shop->slug) }}">
    <img src="{{ $bannerUrl }}" alt="{{ $shop->name }}"
        class="w-32 h-32 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity" />
</a>

<div class="flex-1">
    <div class="flex items-start justify-between mb-2">
        <div class="flex items-center gap-3">
            <img src="{{ $logoUrl }}" alt="{{ $shop->name }}"
                class="w-12 h-12 rounded-full object-cover border-2 border-gray-200" />
            <div>
                <a href="{{ route('shops.show', $shop->slug) }}">
                    <h3
                        class="text-lg font-semibold text-gray-900 cursor-pointer hover:text-green-600 transition-colors">
                        {{ $shop->name }}
                    </h3>
                </a>
                <div class="flex items-center gap-1 text-sm text-gray-600">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>{{ $shop->address }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            @if ($shop->is_verified)
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">
                    ✓ Terverifikasi
                </span>
            @endif
            @if ($shop->is_power_seller)
                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-medium">
                    ⭐ Power Seller
                </span>
            @endif
        </div>
    </div>

    <p class="text-gray-600 text-sm mb-3">{{ $shop->description }}</p>

    <div class="flex items-center gap-6 mb-3 text-sm">
        <div class="flex items-center gap-1">
            <svg class="h-4 w-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                </path>
            </svg>
            <span class="font-medium">{{ $shop->rating ?? 0 }}</span>
            <span class="text-gray-500">({{ $shop->total_reviews ?? 0 }} ulasan)</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span>{{ $shop->products_count ?? 0 }} produk</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
            </svg>
            <span>{{ $shop->response_rate ?? 0 }}% respon</span>
        </div>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('shops.show', $shop->slug) }}"
            class="bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                </path>
            </svg>
            Lihat Toko
        </a>
        <a href="https://wa.me/{{ $shop->phone ?? '6288225292279' }}?text={{ urlencode('Halo ' . $shop->name . ', saya tertarik dengan produk-produk di toko Anda.') }}"
            target="_blank"
            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z">
                </path>
            </svg>
            WhatsApp
        </a>
    </div>
</div>
